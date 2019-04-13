<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Security\Permission\NoPermissionEntryFoundException;

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

interface PermissionListInterface
{
    /**
     * Return all object based entries associated with this.
     *
     * @return array
     */
    public function getEntries(): array;

    /**
     * Return all object field based entries associated with this.
     *
     * @return array
     */
    public function getFieldEntries($field): array;

    /**
     * Return the object identity associated with this.
     *
     * @return ObjectIdentityInterface
     */
    public function getObjectIdentity(): ObjectIdentityInterface;

    /**
     * Return the parent, or null if there is none.
     *
     * @return PermissionListInterface
     */
    public function getParent():? PermissionListInterface;

    /**
     * Whether this is inheriting entries from a parent.
     *
     * @return bool
     */
    public function isEntriesInheriting(): bool;

    /**
     * Determines whether field access is granted.
     *
     * @param string $field
     * @param array $masks
     * @param SubjectIdentity[] $subjectIdentities
     * @param bool $administrativeMode
     * @return bool
     */
    public function isFieldGranted(string $field, array $masks, array $subjectIdentities, $administrativeMode = false): bool;

    /**
     * Determines whether access is granted.
     *
     * @param array $masks
     * @param SubjectIdentity[] $subjectIdentities
     * @param bool $administrativeMode
     * @return bool
     * @throws NoPermissionEntryFoundException when no entry was applicable for this request
     */
    public function isGranted(array $masks, array $securityIdentities, $administrativeMode = false): bool;
}