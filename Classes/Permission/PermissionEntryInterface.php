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

 /**
 * Interface used by permission entry implementations.
 */
interface PermissionEntryInterface
{
    /**
     * Returns the permission mask of this.
     *
     * @return int
     */
    public function getMask(): int;

    /**
     * Returns the subject identity associated with this.
     *
     * @return SubjectIdentityInterface
     */
    public function getSubjectIdentity(): SubjectIdentityInterface;

    /**
     * Returns whether this is granting, or denying.
     *
     * @return bool
     */
    public function isGranting(): bool;

    /**
     * Return the strategy for comparing masks.
     *
     * @return string
     */
    public function getStrategy(): string;

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int;
}