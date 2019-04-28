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

interface MutablePermissionListInterface extends PermissionListInterface
{
    /**
     * Removes an entry.
     *
     * @param PermissionEntryInterface $entry
     */
    public function remove(PermissionEntryInterface $entry);

    /**
     * Adds an entry.
     *
     * @param PermissionEntryInterface $entry
     */
    public function add(PermissionEntryInterface $entry);

    /**
     * Sets whether entries are inherited.
     *
     * @param bool $inheriting
     */
    public function setInheriting(bool $inheriting);

    /**
     * Sets the parent.
     *
     * @param PermissionListInterface|null $parent
     */
    public function setParent(PermissionListInterface $parent = null);
}