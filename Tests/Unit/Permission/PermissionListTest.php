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

/**
 * Testcase for the TYPO3\CMS\Security\Permission\PermissionGrantingStrategy
 */
class PermissionListTest extends UnitTestCase
{
    /**
     * @test
     */
    public function getIteratorSupportsAdd()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);

        $firstEntry = new PermissionEntry(1, $subjectIdentity, 3);
        $secondEntry = new PermissionEntry(1, $subjectIdentity, 2);
        $thirdEntry = new PermissionEntry(1, $subjectIdentity, 1);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy, true);

        $permissionList->add($thirdEntry);
        $permissionList->add($secondEntry);
        $permissionList->add($firstEntry);

        $this->assertEquals(iterator_to_array($permissionList), [$firstEntry, $secondEntry, $thirdEntry]);
    }

    /**
     * @test
     */
    public function getIteratorSupportsRemove()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);

        $firstEntry = new PermissionEntry(1, $subjectIdentity, 100);
        $secondEntry = new PermissionEntry(1, $subjectIdentity, 10);
        $thirdEntry = new PermissionEntry(1, $subjectIdentity, 1);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy, true);

        $permissionList->add($thirdEntry);
        $permissionList->add($secondEntry);
        $permissionList->add($firstEntry);

        $permissionList->remove($secondEntry);

        $this->assertEquals(iterator_to_array($permissionList), [$firstEntry, $thirdEntry]);
    }

    /**
     * @test
     */
    public function getIteratorRespectsEntryPriority()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = new BackendUserIdentity(1);

        $firstEntry = new PermissionEntry(1, $subjectIdentity, -1);
        $secondEntry = new PermissionEntry(1, $subjectIdentity, -10);
        $thirdEntry = new PermissionEntry(1, $subjectIdentity, -100);

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy, true);

        $permissionList->add($thirdEntry);
        $permissionList->add($secondEntry);
        $permissionList->add($firstEntry);

        $this->assertEquals(iterator_to_array($permissionList), [$firstEntry, $secondEntry, $thirdEntry]);

        $thirdEntry->setPriority(0);

        $this->assertEquals(iterator_to_array($permissionList), [$thirdEntry, $firstEntry, $secondEntry]);
    }
}