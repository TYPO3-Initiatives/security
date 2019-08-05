<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Policy\ExpressionLanguage;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\EntityResourceAttribute;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\LanguageResourceAttribute;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\PropertyDefinitionResourceAttribute;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\ReadActionAttribute;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\WriteActionAttribute;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ActionAttribute;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ResourceAttribute;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\SubjectAttribute;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\PermissionEvaluatorInterface;

/**
 * @internal
 * @todo Should be part of the extension `backend`
 */
class PermissionEvaluator implements PermissionEvaluatorInterface
{
    /**
     * @var array
     */
    protected static $languages;

    /**
     * @var array
     */
    protected static $permissions;

    /**
     * @inheritdoc
     */
    public function canEvaluate(ResourceAttribute $resource, ActionAttribute $action): bool
    {
        return $resource instanceof EntityResourceAttribute
            || $resource instanceof PropertyDefinitionResourceAttribute
            || $resource instanceof EntityResourceAttribute;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(SubjectAttribute $subject, ResourceAttribute $resource, ActionAttribute $action): bool
    {
        $permissions = $this->getPermissions();

        foreach ($subject->principals as $principal) {
            if ($permissions[$principal->identifier][$resource->type][$resource->identifier][$action->identifier] ?? false === true) {
                return true;
            }
        }

        return false;
    }

    protected function getLanguages(): array
    {
        if (self::$languages === null) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_language');
            $queryBuilder->select('uid')
                ->from('sys_language')
                ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter(0, \PDO::PARAM_INT)));
            self::$languages = array_merge([['uid' => 0]], $queryBuilder->execute()->fetchAll(), [['uid' => -1]]);
        }

        return self::$languages;
    }

    protected function getPermissions(): array
    {
        if (self::$permissions === null) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('be_groups');
            $expressionBuilder = $queryBuilder->expr();
            $ressource = $queryBuilder->select(
                    'uid',
                    'tables_select',
                    'tables_modify',
                    'non_exclude_fields',
                    'explicit_allowdeny',
                    'allowed_languages'
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
                $principal = (string) $row['uid'];

                foreach ([
                    'tables_select' => ReadActionAttribute::TYPE,
                    'tables_modify' => WriteActionAttribute::TYPE,
                ] as $field => $action) {
                    foreach (array_filter(explode(',', $row[$field])) as $table) {
                        self::$permissions[$principal][EntityResourceAttribute::TYPE][$table][$action] = true;
                    };
                }

                foreach (array_filter(explode(',', $row['non_exclude_fields'])) as $field) {
                    list($table, $field) = explode(':', $field);
                    self::$permissions[$principal][PropertyDefinitionResourceAttribute::TYPE][$table][$field] = [
                        ReadActionAttribute::TYPE => true,
                        WriteActionAttribute::TYPE => true,
                    ];
                }

                foreach (empty(trim($row['allowed_languages'])) ? $this->getLanguages()
                    : array_filter(explode(',', $row['allowed_languages'])) as $language) {
                    self::$permissions[$principal][LanguageResourceAttribute::TYPE][(string) $language] = [
                        ReadActionAttribute::TYPE => true,
                        WriteActionAttribute::TYPE => true,
                    ];
                }

                foreach (array_filter(explode(',', $row['explicit_allowdeny'])) as $entry) {
                    list($table, $field, $enumeral, $effect) = explode(':', $entry);
                    $mode = $GLOBALS['TCA'][$table]['columns'][$field]['config']['authMode'] ?? '';
                    $items = $GLOBALS['TCA'][$table]['columns'][$field]['config']['items'] ?? [];
                    if ($effect === 'ALLOW' && $mode === 'explicitAllow'
                        || $effect === 'DENY' && $mode === 'explicitDeny'
                        || array_reduce($items, function ($decision, $item) use ($enumeral, $effect) {
                            return $decision || $item[1] ?? $enumeral === null && $item[4] ?? $effect === null;
                        }, false)
                    ) {
                        continue;
                    }

                    self::$permissions[$principal][EnumeralDefinitionResourceAttribute::TYPE][$table][$field][$enumeral] = [
                        ReadActionAttribute::TYPE => true,
                        WriteActionAttribute::TYPE => true,
                    ];
                }
            }
        }

        return self::$permissions;
    }
}