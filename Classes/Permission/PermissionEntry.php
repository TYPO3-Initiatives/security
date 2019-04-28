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
 * A basic permission entry implementation.
 */
class PermissionEntry implements MutablePermissionEntryInterface
{
    private $granting;

    private $subjectIdentity;

    private $mask;

    private $strategy;

    private $priority;

    /**
     * @param int $mask
     * @param SubjectIdentityInterface $permissionGrantingStrategy
     * @param int $priority
     * @param string $strategy
     * @param bool $granting
     */
    public function __construct(int $mask, SubjectIdentityInterface $subjectIdentity, int $priority = 1, string $strategy = PermissionGrantingStrategy::ALL, bool $granting = true)
    {
        $this->mask = $mask;
        $this->subjectIdentity = $subjectIdentity;
        $this->priority = $priority;
        $this->strategy = $strategy;
        $this->granting = $granting;
    }

    /**
     * {@inheritdoc}
     */
    public function getMask(): int
    {
        return $this->mask;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubjectIdentity(): SubjectIdentityInterface
    {
        return $this->subjectIdentity;
    }

    /**
     * {@inheritdoc}
     */
    public function getStrategy(): string
    {
        return $this->strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranting(): bool
    {
        return $this->granting;
    }

    /**
     * {@inheritdoc}
     */
    public function setMask(int $mask)
    {
        $this->mask = $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function setSubjectIdentity(SubjectIdentityInterface $subjectIdentity)
    {
        $this->subjectIdentity = $subjectIdentity;
    }

    /**
     * {@inheritdoc}
     */
    public function setGranting(bool $granting)
    {
        $this->granting = $granting;
    }

    /**
     * {@inheritdoc}
     */
    public function setStrategy(string $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * {@inheritdoc}
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
    }
}
