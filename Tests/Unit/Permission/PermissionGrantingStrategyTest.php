<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Tests\Unit\Permission;

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

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3\CMS\Security\Permission\PermissionList;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\PermissionGrantingStrategy;
use TYPO3\CMS\Security\Permission\PermissionEntry;
use TYPO3\CMS\Security\Permission\BackendUserIdentity;
use TYPO3\CMS\Security\Permission\BackendGroupIdentity;
use TYPO3\CMS\Security\Permission\Exception\NoPermissionEntryFoundException;

/**
 * Testcase for the TYPO3\CMS\Security\Permission\PermissionGrantingStrategy
 */
class PermissionGrantingStrategyTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isGrantedFavorsLocalEntriesOverParentEntries()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);
        $permissionList->add(new PermissionEntry(1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $permissionList->setParent(new PermissionList(new ObjectIdentity('bar'), $strategy));
        $permissionList->getParent()->add(new PermissionEntry(1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, false));

        $this->assertTrue($strategy->isGranted($permissionList, [1], [$subjectIdentity]));
    }

    /**
     * @test
     */
    public function isGrantedFallsBackToParentEntriesIfNoLocalEntriesAreApplicable()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);
        $anotherSubjectIdentity = new BackendUserIdentity(2);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy, true);
        $permissionList->add(new PermissionEntry(1, $anotherSubjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $permissionList->setParent(new PermissionList(new ObjectIdentity('bar'), $strategy));
        $permissionList->getParent()->add(new PermissionEntry(1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $this->assertTrue($strategy->isGranted($permissionList, [1], [$subjectIdentity]));
    }

    /**
     * @test
     */
    public function isGrantedThrowsExceptionIfNoEntryIsFound()
    {
        $this->expectException(NoPermissionEntryFoundException::class);

        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $strategy->isGranted($permissionList, [1], [$subjectIdentity]);
    }

    /**
     * @test
     */
    public function isGrantedUsesFirstApplicableEntryToMakeUltimateDecisionForPermissionIdentityCombination()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);
        $anotherSubjectIdentity = new BackendGroupIdentity(1);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $permissionList->add(new PermissionEntry(1, $anotherSubjectIdentity, 100, PermissionGrantingStrategy::ALL, true));
        $permissionList->add(new PermissionEntry(1, $subjectIdentity, 10, PermissionGrantingStrategy::ALL, false));
        $permissionList->add(new PermissionEntry(1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $this->assertFalse($strategy->isGranted($permissionList, [1], [$subjectIdentity, $anotherSubjectIdentity]));
    }

    /**
     * @test
     * @dataProvider isGrantedSupportsDifferentStrategiesDataProvider
     */
    public function isGrantedSupportsDifferentStrategies($maskStrategy, $mask, $requiredMask, $result)
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $permissionList->add(new PermissionEntry($mask, $subjectIdentity, 1, $maskStrategy, true));

        if ($result) {
            $this->assertTrue($strategy->isGranted($permissionList, [$requiredMask], [$subjectIdentity]));
        } else {
            $this->expectException(NoPermissionEntryFoundException::class);
            $strategy->isGranted($permissionList, [$requiredMask], [$subjectIdentity]);
        }
    }

    public function isGrantedSupportsDifferentStrategiesDataProvider()
    {
        return [
            ['all', 1 << 0 | 1 << 1, 1 << 0, true],
            ['all', 1 << 0 | 1 << 1, 1 << 2, false],
            ['all', 1 << 0 | 1 << 10, 1 << 0 | 1 << 10, true],
            ['all', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1 | 1 << 2, false],
            ['any', 1 << 0 | 1 << 1, 1 << 0, true],
            ['any', 1 << 0 | 1 << 1, 1 << 0 | 1 << 2, true],
            ['any', 1 << 0 | 1 << 1, 1 << 2, false],
            ['equal', 1 << 0 | 1 << 1, 1 << 0, false],
            ['equal', 1 << 0 | 1 << 1, 1 << 1, false],
            ['equal', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1, true],
        ];
    }
}