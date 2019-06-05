<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Backend\Permission;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\MetaModel\EntityRelationMapFactory;
use TYPO3\CMS\Security\Permission\AnyObjectIdentity;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\ObjectIdentityInterface;
use TYPO3\CMS\Security\Permission\PermissionEntry;
use TYPO3\CMS\Security\Permission\PermissionFieldEntry;
use TYPO3\CMS\Security\Permission\PermissionGrantingStrategy;
use TYPO3\CMS\Security\Permission\PermissionList;
use TYPO3\CMS\Security\Permission\PermissionListInterface;
use TYPO3\CMS\Security\Permission\PermissionRetrivalStrategyInterface;
use Webmozart\Assert\Assert;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

 /**
  * Strategy to retrive permission lists of managed tables.
  *
  * @todo Improve caching.
  */
class TablePermissionRetrivalStrategy implements PermissionRetrivalStrategyInterface
{
    const PERMISSION_READ = 1;

    const PERMISSION_WRITE = 2;

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    protected $cache;

    /**
     * @var \TYPO3\CMS\Core\Configuration\MetaModel\EntityRelationMap
     */
    protected $entityRelationMap;

    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('security_permission');
        $this->entityRelationMap = GeneralUtility::makeInstance(EntityRelationMapFactory::class, $GLOBALS['TCA'])->create();
    }

    /**
     * {@inheritdoc}
     */
    public function canRetrive(ObjectIdentityInterface $objectIdentity): bool
    {
        return substr($objectIdentity->getIdentifier(), 0, strlen('table')) === 'table';
    }

    /**
     * {@inheritdoc}
     */
    public function retrive(ObjectIdentityInterface $objectIdentity, array $subjectIdentities = []): PermissionListInterface
    {
        $hash = sha1(sprintf('%s %s', (string)$objectIdentity, implode(' ', $subjectIdentities)));

        if (!$this->cache->has($hash)) {
            $table = $this->getTable($objectIdentity);
            $masks = $this->getMasks();
            $priority = count($subjectIdentities);
            $permissionList = new PermissionList($objectIdentity, new PermissionGrantingStrategy(), true);

            $permissionList->setParent($this->getParent($objectIdentity));

            foreach ($subjectIdentities as $subjectIdentity) {
                $key = (string)$subjectIdentity;

                if (isset($masks[$key][$table]['table'])) {
                    $permissionList->add(new PermissionEntry(
                        $masks[$key][$table]['table'],
                        $subjectIdentity,
                        $priority * 10
                    ));
                }

                if (isset($masks[$key][$table]['fields'])) {
                    foreach ($masks[$key][$table]['fields'] as $field => $mask) {
                        $permissionList->add(new PermissionFieldEntry(
                            new ObjectIdentity(sprintf('table/%s/field/%s', $table, $field)),
                            $mask,
                            $subjectIdentity,
                            $priority * 10
                        ));
                    }
                }

                --$priority;
            }

            $this->cache->set($hash, $permissionList);
        }

        return $this->cache->get($hash);
    }

    /**
     * Returns the default permission list for table access.
     *
     * @param ObjectIdentityInterface $objectIdentiy
     * @return PermissionListInterface
     */
    protected function getParent(ObjectIdentityInterface $objectIdentity): PermissionListInterface
    {
        $hash = sha1((string)$objectIdentity);

        if (!$this->cache->has($hash)) {
            $table = $this->getTable($objectIdentity);
            $permissionList = new PermissionList($objectIdentity, new PermissionGrantingStrategy());

            $permissionList->add(new PermissionEntry(
                self::PERMISSION_READ | self::PERMISSION_WRITE,
                new BackendAdministratorIdentity(),
                30,
                PermissionGrantingStrategy::ALL
            ));

            $permissionList->add(new PermissionFieldEntry(
                new AnyObjectIdentity(),
                self::PERMISSION_READ | self::PERMISSION_WRITE,
                new BackendAdministratorIdentity(),
                30,
                PermissionGrantingStrategy::ALL
            ));

            $permissionList->add(new PermissionEntry(
                self::PERMISSION_READ | self::PERMISSION_WRITE,
                new BackendAuthenticationIdentity(),
                10,
                PermissionGrantingStrategy::ALL,
                false
            ));

            $entityDefinition = $this->entityRelationMap->getEntityDefinition($table);

            Assert::notNull($entityDefinition, sprintf('Unknown table %s', $table));

            foreach ($entityDefinition->getPropertyDefinitions() as $propertyDefinition) {
                $configuration = $propertyDefinition->getConfiguration();

                if ($configuration['exclude']) {
                    $permissionList->add(new PermissionFieldEntry(
                        new ObjectIdentity(sprintf('table/%s/field/%s', $table, $propertyDefinition->getName())),
                        self::PERMISSION_READ | self::PERMISSION_WRITE,
                        new BackendAuthenticationIdentity(),
                        20,
                        PermissionGrantingStrategy::ALL,
                        false
                    ));
                }
            }

            $permissionList->add(new PermissionFieldEntry(
                new AnyObjectIdentity(),
                self::PERMISSION_READ | self::PERMISSION_WRITE,
                new BackendAuthenticationIdentity(),
                10,
                PermissionGrantingStrategy::ALL
            ));

            $this->cache->set($hash, $permissionList);
        }

        return $this->cache->get($hash);
    }

    protected function getTable(ObjectIdentityInterface $objectIdentity)
    {
        $segments = explode('/', trim(parse_url($objectIdentity->getIdentifier(), \PHP_URL_PATH), '/'), 4);

        return $segments[1];
    }

    protected function getMasks(): array
    {
        if (!$this->cache->has('masks')) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');
            $expressionBuilder = $queryBuilder->expr();
            $ressource = $queryBuilder->select('uid', 'tables_select', 'tables_modify', 'non_exclude_fields')
                ->from('be_groups')
                ->where($expressionBuilder->andX(
                    $expressionBuilder->eq(
                        'pid',
                        $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                    ),
                    $expressionBuilder->orX(
                        $expressionBuilder->eq('lockToDomain', $queryBuilder->quote('')),
                        $expressionBuilder->isNull('lockToDomain'),
                        $expressionBuilder->eq(
                            'lockToDomain',
                            $queryBuilder->createNamedParameter(GeneralUtility::getIndpEnv('HTTP_HOST'), \PDO::PARAM_STR)
                        )
                    )
                ))
                ->execute();

            $masks = [];

            while ($row = $ressource->fetch(\PDO::FETCH_ASSOC)) {
                $subjectIdentity = new BackendGroupIdentity((int)$row['uid']);
                $key = (string)$subjectIdentity;

                foreach ([
                    'tables_select' => self::PERMISSION_READ,
                    'tables_modify' => self::PERMISSION_WRITE
                ] as $field => $mask) {
                    foreach (array_filter(explode(',', $row[$field])) as $table) {
                        $masks[$key][$table]['table'] |= $mask;
                    };
                }

                foreach (array_filter(explode(',', $row['non_exclude_fields'])) as $field) {
                    list($table, $field) = explode(':', $field);
                    $masks[$key][$table]['fields'][$field] = $masks[$key][$table]['table'];
                }
            }

            $this->cache->set('masks', $masks);
        }

        $masks = $this->cache->get('masks');

        return $masks;
    }
}