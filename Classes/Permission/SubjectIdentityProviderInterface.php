<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Security\Permission\PermissionListNotFoundException;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

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

interface SubjectIdentityProviderInterface
{
    /**
     * Returns the list of subject identites that belongs to the given authentication.
     *
     * @param AbstractUserAuthentication $authentication
     * @return SubjectIdentityInterface[]
     */
    public function getSubjectIdentities(AbstractUserAuthentication $authentication): array;
}