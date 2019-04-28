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
 * Interface used by permission granting implementations.
 */
interface PermissionGrantingStrategyInterface
{
    /**
     * Determines whether access to a domain object is to be granted.
     *
     * @param PermissionListInterface $list
     * @param array $masks
     * @param array $subjectIdentities
     * @return bool
     */
    public function isGranted(PermissionListInterface $list, array $masks, array $subjectIdentities): bool;

    /**
     * Determines whether access to a domain object's field is to be granted.
     *
     * @param PermissionListInterface $list
     * @param string $field
     * @param array $masks
     * @param array $subjectIdentities
     * @return bool
     */
    public function isFieldGranted(PermissionListInterface $list, string $field, array $masks, array $subjectIdentities): bool;
}