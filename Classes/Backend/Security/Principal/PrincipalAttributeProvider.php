<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Security\Principal;

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
use TYPO3\CMS\Backend\Security\Attribute\RoleAttribute;
use TYPO3\CMS\Backend\Security\Attribute\UserAttribute;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Event\SubjectRetrivalEvent;

/**
 * @internal
 * @todo Move into extension `backend`.
 * @todo Fetch system maintainer role using context.
 */
class PrincipalAttributeProvider
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @inheritdoc
     */
    public function __invoke(SubjectRetrivalEvent $event): void
    {
        if (!$event->getContext()->getAspect('backend.user')->get('isLoggedIn')) {
            return;
        }

        $subject = $event->getSubject();
        $userAspect = $event->getContext()->getAspect('backend.user');
        $cacheIdentifier = sha1(static::class . '_user_' . $userAspect->get('id'));

        if (($entry = $this->cache->get($cacheIdentifier)) === false) {
            $entry = [];

            $entry[] = new UserAttribute(
                $userAspect->get('id'),
                $userAspect->get('username')
            );
    
            foreach ($userAspect->get('groupIds') as $groupId) {
                $entry[] = new GroupAttribute($groupId, $this->getGroupTitle($groupId));
            }
    
            if ($userAspect->get('isAdmin')) {
                $entry[] = new RoleAttribute('ADMIN');
            }

            /*if ($userAspect->get('isSystemMaintainer')) {
                $subject->principals[] = new RolePrincipalAttribute('SYSTEM_MAINTAINER');
            }*/
            
            $this->cache->set($cacheIdentifier, $entry);
        }

        $subject->principals = array_merge($subject->principals, $entry);
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
