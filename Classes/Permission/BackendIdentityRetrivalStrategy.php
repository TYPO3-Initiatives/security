<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
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

class BackendIdentityRetrivalStrategy implements SubjectIdentityRetrivalStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canRetrive(AbstractUserAuthentication $authentication): bool
    {
        return $authentication instanceof BackendUserAuthentication;
    }

    /**
     * {@inheritdoc}
     */
    public function retrive(AbstractUserAuthentication $authentication): array
    {
        Assert::isInstanceOf($authentication, BackendUserAuthentication::class);

        $subjectIdentities = [];

        if ($authentication->isAdmin()) {
            $subjectIdentities[] = new BackendAdministratorIdentity();
        }

        foreach ($authentication->userGroupsUID as $userGroupUid) {
            $subjectIdentities[] = new BackendGroupIdentity((int)$userGroupUid);
        }

        $subjectIdentities[] = new BackendUserIdentity((int)$authentication->user['uid']);
        $subjectIdentities[] = new BackendAuthenticationIdentity();

        return $subjectIdentities;
    }
}