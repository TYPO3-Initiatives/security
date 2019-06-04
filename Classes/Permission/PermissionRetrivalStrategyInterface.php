<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Security\Permission\PermissionListNotFoundException;

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

interface PermissionRetrivalStrategyInterface
{
    /**
     * Whether this is able to find a permission list for the given object identity or not.
     *
     * @param ObjectIdentityInterface $objectIdentity
     * @return bool
     */
    public function canRetrive(ObjectIdentityInterface $objectIdentity): bool;

    /**
     * Returns the list that belongs to the given object identity.
     *
     * @param ObjectIdentityInterface $objectIdentity
     * @param SubjectIdentityInterface[] $subjectIdentities
     * @return PermissionListInterface
     * @throws PermissionListNotFoundException when there is no list
     */
    public function retrive(ObjectIdentityInterface $objectIdentity, array $subjectIdentities = []): PermissionListInterface;
}