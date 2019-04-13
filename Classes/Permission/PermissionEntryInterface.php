<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

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

interface PermissionEntryInterface
{
    /**
     * Return the list this is associated with.
     *
     * @return PermissionListInterface
     */
    public function getList(): PermissionListInterface;

    /**
     * Return the primary key of this.
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Return the permission mask of this.
     *
     * @return int
     */
    public function getMask(): int;

    /**
     * Return the subject identity associated with this.
     *
     * @return SubjectIdentityInterface
     */
    public function getSubjectIdentity(): SubjectIdentityInterface;

    /**
     * Returnswhether this is granting, or denying.
     *
     * @return bool
     */
    public function isGranting(): bool;
}