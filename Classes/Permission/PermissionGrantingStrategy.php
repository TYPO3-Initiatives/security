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

use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Security\Permission\Exception\NoPermissionEntryFoundException;

/**
 * Permission granting strategy to apply to the permission list.
 */
class PermissionGrantingStrategy implements PermissionGrantingStrategyInterface
{
    /**
     * The entry will be considered applicable when any of the turned-on bits in
     * the required mask is also turned-on the in the entry mask.
     */
    const EQUAL = 'equal';

    /**
     * The entry will be considered applicable when all the turned-on bits in the
     * required mask are also turned-on in the entry mask.
     */
    const ALL = 'all';

    /**
     * The ACE will be considered applicable when the bitmasks are equal.
     */
    const ANY = 'any';

    /**
     * {@inheritdoc}
     */
    public function isGranted(PermissionListInterface $list, array $masks, array $subjectIdentities): bool
    {
        while ($list) {
            try {
                $entries = new class(new \IteratorIterator($list)) extends \FilterIterator {
                    public function accept() {
                        return !($this->getInnerIterator()->current() instanceof PermissionFieldEntryInterface);
                    }
                };
                return $this->hasSufficientPermissions($entries, $masks, $subjectIdentities);
            } catch (NoPermissionEntryFoundException $e) {
                if (!$list->isInheriting() || $list->getParent() === null) {
                    throw $e;
                }
                $list = $list->getParent();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isFieldGranted(PermissionListInterface $list, string $field, array $masks, array $subjectIdentities): bool
    {
        while ($list) {
            try {
                $entries = new class(new \IteratorIterator($list)) extends \FilterIterator {
                    public function accept() {
                        return $this->getInnerIterator()->current() instanceof PermissionFieldEntryInterface;
                    }
                };
                return $this->hasSufficientPermissions($entries, $masks, $subjectIdentities);
            } catch (NoPermissionEntryFoundException $e) {
                if (!$list->isInheriting() || $list->getParent() === null) {
                    throw $e;
                }
                $list = $list->getParent();
            }
        }
    }

    /**
     * Makes an authorization decision.
     *
     * @param \Iterator $entries
     * @param array $masks
     * @param SubjectIdentityInterface[] $subjectIdentities
     * @return bool
     * @throws NoPermissionEntryFoundException
     */
    private function hasSufficientPermissions(\Iterator $entries, array $masks, array $subjectIdentities)
    {
        $firstRejectedEntry = null;

        foreach ($masks as $requiredMask) {
            foreach ($subjectIdentities as $subjectIdentity) {
                foreach ($entries as $entry) {
                    if ($subjectIdentity->equals($entry->getSubjectIdentity()) && $this->isEntryApplicable((int)$requiredMask, $entry)) {
                        if ($entry->isGranting()) {
                            return true;
                        }

                        if ($firstRejectedEntry === null) {
                            $firstRejectedEntry = $entry;
                        }

                        break 2;
                    }
                }
            }
        }

        if ($firstRejectedEntry !== null) {
            return false;
        }

        throw new NoPermissionEntryFoundException();
    }

    /**
     * Determines whether the entry is applicable to the given permission/subject
     * identity combination.
     *
     * @param int $requiredMask
     * @param PermissionEntryInterface $entry
     * @return bool
     * @throws Exception if the entry strategy is not supported
     */
    private function isEntryApplicable(int $requiredMask, PermissionEntryInterface $entry)
    {
        $strategy = $entry->getStrategy();

        if (self::ALL === $strategy) {
            return $requiredMask === ($entry->getMask() & $requiredMask);
        } elseif (self::ANY === $strategy) {
            return 0 !== ($entry->getMask() & $requiredMask);
        } elseif (self::EQUAL === $strategy) {
            return $requiredMask === $entry->getMask();
        }

        throw new Exception(sprintf('The strategy %s is not supported.', $strategy), 1556442675);
    }
}
