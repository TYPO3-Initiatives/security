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
 * A basic permission field entry implementation.
 */
class PermissionFieldEntry extends PermissionEntry implements MutablePermissionFieldEntryInterface
{
    /**
     * @var ObjectIdentityInterface
    */
    private $fieldIdentity;

    /**
     * @param int $mask
     * @param SubjectIdentityInterface $permissionGrantingStrategy
     * @param bool $granting
     */
    public function __construct(ObjectIdentityInterface $fieldIdentity, int $mask, SubjectIdentityInterface $subjectIdentity, int $priority = 1, string $strategy = PermissionGrantingStrategy::ALL, bool $granting = true)
    {
        parent::__construct($mask, $subjectIdentity, $priority, $strategy, $granting);

        $this->fieldIdentity = $fieldIdentity;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldIdentity(): ObjectIdentityInterface
    {
        return $this->fieldIdentity;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldIdentity(ObjectIdentityInterface $fieldIdentity)
    {
        return $this->fieldIdentity = $fieldIdentity;
    }
}
