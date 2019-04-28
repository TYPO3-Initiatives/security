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

interface MutablePermissionEntryInterface extends PermissionEntryInterface
{
    /**
     * Sets the permission mask of this.
     *
     * @param int $mask
     */
    public function setMask(int $mask);

    /**
     * Sets the subject identity associated with this.
     *
     * @return SubjectIdentityInterface
     */
    public function setSubjectIdentity(SubjectIdentityInterface $subjectIdentity);

    /**
     * Sets whether this is granting, or denying.
     *
     * @param bool $granting
     */
    public function setGranting(bool $granting);

    /**
     * Sets the strategy for comparing masks.
     *
     * @param string $strategy
     */
    public function setStrategy(string $strategy);

    /**
     * Sets the priority.
     *
     * @param int $priority
     */
    public function setPriority(int $priority);
}