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

use TYPO3\CMS\Backend\Policy\ExpressionLanguage\Attribute\GroupPrincipalAttribute;
use TYPO3\CMS\Backend\Policy\ExpressionLanguage\Attribute\RolePrincipalAttribute;
use TYPO3\CMS\Backend\Policy\ExpressionLanguage\Attribute\UserPrincipalAttribute;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\PrincipalProviderInterface;

/**
 * @internal
 * @todo Should be part of the extension `backend`
 */
class PrincipalProvider implements PrincipalProviderInterface
{
    /**
     * @inheritdoc
     */
    public function provide(Context $context): array
    {
        $backendUserAuthentication = $this->getBackendUserAuthentication($context);
        $backendUserIdentifier = (int)($backendUserAuthentication->user[$backendUserAuthentication->userid_column ?? 'uid'] ?? 0);
        $principals = [];

        if ($backendUserAuthentication && $backendUserIdentifier > 0) {
            $principals[] = new UserPrincipalAttribute(
                $backendUserIdentifier,
                (string)($backendUserAuthentication->user[$backendUserAuthentication->username_column ?? 'username'] ?? '')
            );

            foreach ($backendUserAuthentication->userGroups as $userGroup) {
                $principals[] = new GroupPrincipalAttribute((int) $userGroup['uid'], $userGroup['title']);
            }

            if ($backendUserAuthentication->isAdmin()) {
                $principals[] = new RolePrincipalAttribute('ADMIN');
            }

            if ($backendUserAuthentication->isSystemMaintainer()) {
                $principals[] = new RolePrincipalAttribute('SYSTEM_MAINTAINER');
            }
        }

        return $principals;
    }

    /**
     * @todo Respect given context
     */
    protected function getBackendUserAuthentication(Context $context): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}
