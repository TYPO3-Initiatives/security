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
 * A basic permission list implementation.
 */
class PermissionList implements MutablePermissionListInterface
{
    private $parent;

    private $permissionGrantingStrategy;

    private $objectIdentity;

    private $entries = [];

    private $inheriting;

    /**
     * @param ObjectIdentityInterface $objectIdentity
     * @param PermissionGrantingStrategyInterface $permissionGrantingStrategy
     * @param array $loadedSids
     * @param bool $inheriting
     */
    public function __construct(ObjectIdentityInterface $objectIdentity, PermissionGrantingStrategyInterface $permissionGrantingStrategy, $inheriting = false)
    {
        $this->objectIdentity = $objectIdentity;
        $this->permissionGrantingStrategy = $permissionGrantingStrategy;
        $this->inheriting = $inheriting;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(PermissionEntryInterface $entry)
    {
        $hash = \spl_object_hash($entry);

        if (isset($this->entries[$hash])) {
            unset($this->entries[$hash]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        $entries = $this->entries;

        usort($entries, function($a, $b) {
            if ($a->getPriority() === $b->getPriority()) {
                return 0;
            }
            return ($a->getPriority() > $b->getPriority()) ? -1 : 1;
        });

        return new \ArrayIterator($entries);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity(): ObjectIdentityInterface
    {
        return $this->objectIdentity;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent():? PermissionListInterface
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function add(PermissionEntryInterface $entry)
    {
        $this->entries[\spl_object_hash($entry)] = $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function isInheriting(): bool
    {
        return $this->inheriting;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted(array $masks, array $subjectIdentities, ?ObjectIdentity $fieldIdentity = null): bool
    {
        return $this->permissionGrantingStrategy->isGranted($this, $masks, $subjectIdentities, $fieldIdentity);
    }

    /**
     * {@inheritdoc}
     */
    public function setInheriting(bool $inheriting)
    {
        if ($this->inheriting !== $inheriting) {
            $this->inheriting = $inheriting;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(PermissionListInterface $parent = null)
    {
        if ($this->parent !== $parent) {
            $this->parent = $parent;
        }
    }
}
