<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Security\Permission;

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

use TYPO3\CMS\Backend\Security\Attribute\GroupAttribute;
use TYPO3\CMS\Backend\Security\Attribute\PermissionAttribute;
use TYPO3\CMS\Backend\Security\Attribute\ResourceAttribute;
use TYPO3\CMS\Backend\Security\Attribute\UserAttribute;
use TYPO3\CMS\Backend\Security\Permission\PermissionConfigurationLoader;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Event\AttributeRetrivalEvent;

/**
 * @internal
 * @todo Move into extension `backend`.
 */
class PermissionAttributeProvider
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var array
     */
    private $permissionsConfiguration;

    public function __construct(FrontendInterface $cache)
    {
        $permissionConfigurationLoader = GeneralUtility::makeInstance(PermissionConfigurationLoader::class);

        $this->cache = $cache;
        $this->permissionsConfiguration = $permissionConfigurationLoader->getPermissionConfiguration();
    }

    /**
     * @inheritdoc
     */
    public function __invoke(AttributeRetrivalEvent $event): void
    {
        if (!$event->getAttribute() instanceof ResourceAttribute) {
            return;
        }

        if (!$event->getContext()->getAspect('backend.user')->get('isLoggedIn')) {
            return;
        }

        $attribute = $event->getAttribute();
        $userAspect = $event->getContext()->getAspect('backend.user');
        $cacheIdentifier = sha1(static::class . '_user_' . $userAspect->get('id'));

        if (($entry = $this->cache->get($cacheIdentifier)) === false) {
            $resources = array_merge(
                $this->permissionsConfiguration[$attribute->class]['dependencies'] ?? [],
                [$attribute->class]
            );
            $entry = [];

            foreach ($resources as $resource) {
                foreach ($this->getUserPermissions($userAspect->get('id'), $resource) as $permission) {
                    $entry[] = new PermissionAttribute(
                        new UserAttribute($userAspect->get('id'), $userAspect->get('username')),
                        $resource,
                        $permission['action'],
                        $permission['state']
                    );
                }
                foreach ($userAspect->get('groupIds') as $groupId) {
                    foreach ($this->getGroupPermissions($groupId, $resource) as $permission) {
                        $entry[] = new PermissionAttribute(
                            new GroupAttribute($groupId, $this->getGroupTitle($groupId)),
                            $resource,
                            $permission['action'],
                            $permission['state']
                        );
                    }
                }
            }

            $this->cache->set($cacheIdentifier, $entry);
        }

        $attribute->permissions = array_merge($attribute->permissions, $entry);
    }

    protected function getGroupPermissions(int $groupId, string $resource): array
    {
        $cacheIdentifier = sha1(static::class . '_group_permissions');

        if (($entry = $this->cache->get($cacheIdentifier)) === false) {
            $entry = [];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');
            $expressionBuilder = $queryBuilder->expr();
            $ressource = $queryBuilder->select(
                    'uid',
                    'permissions'
                )
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

            while ($row = $ressource->fetch(\PDO::FETCH_ASSOC)) {
                $entry[$row['uid']] = json_decode((string)$row['permissions'], true);
            }

            $this->cache->set($cacheIdentifier, $entry);
        }

        return $entry[(string)$groupId][$resource] ?? [];
    }

    protected function getUserPermissions(int $userId, string $resource): array
    {
        $cacheIdentifier = sha1(static::class . '_user_permissions');

        if (($entry = $this->cache->get($cacheIdentifier)) === false) {
            $cacheEntry = [];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_users');
            $expressionBuilder = $queryBuilder->expr();
            $ressource = $queryBuilder->select(
                    'uid',
                    'permissions'
                )
                ->from('be_users')
                ->where($expressionBuilder->eq(
                    'pid',
                    $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)
                ))
                ->execute();

            while ($row = $ressource->fetch(\PDO::FETCH_ASSOC)) {
                $entry[$row['uid']] = json_decode((string)$row['permissions'], true);
            }

            $this->cache->set($cacheIdentifier, $entry);
        }

        return $entry[(string)$userId][$resource] ?? [];
    }

    protected function getGroupTitle(int $groupId): string
    {
        $cacheIdentifier = sha1(static::class . '_group_titles');

        if (($entry = $this->cache->get($cacheIdentifier)) === false) {
            $entry = [];
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');
            $expressionBuilder = $queryBuilder->expr();
            $ressource = $queryBuilder->select(
                    'uid',
                    'title'
                )
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

            while ($row = $ressource->fetch(\PDO::FETCH_ASSOC)) {
                $entry[$row['uid']] = $row['title'];
            }

            $this->cache->set($cacheIdentifier, $entry);
        }

        return $entry[(string)$groupId] ?? '';
    }
}
