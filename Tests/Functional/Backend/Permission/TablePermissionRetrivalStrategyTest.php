<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Backend\Tests\Functional\Permission;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Permission\TablePermissionRetrivalStrategy;
use TYPO3\CMS\Security\Permission\SubjectIdentityProvider;
use TYPO3\CMS\Security\Permission\PermissionProvider;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class TablePermissionRetrivalStrategyTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3/sysext/security'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Permission.csv');
    }

    public function checkTablePermissionsProvider()
    {
        return [
            [2, false, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'pages'],
            [2, false, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'pages'],
            [2, false, [TablePermissionRetrivalStrategy::PERMISSION_READ, TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'pages'],
            [1, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'be_users'],
            [1, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'be_users'],
            [1, true, [TablePermissionRetrivalStrategy::PERMISSION_READ, TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'be_users'],
            [3, false, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'sys_log'],
            [3, false, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'sys_log'],
            [3, false, [TablePermissionRetrivalStrategy::PERMISSION_WRITE, TablePermissionRetrivalStrategy::PERMISSION_READ], 'sys_log'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'tt_content'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'tt_content'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE, TablePermissionRetrivalStrategy::PERMISSION_READ], 'tt_content'],
        ];
    }

    /**
     * @test
     * @dataProvider checkTablePermissionsProvider
     */
    public function checkTablePermissions(int $backendUser, bool $granted, array $masks, string $table)
    {
        $this->setUpBackendUserFromFixture($backendUser);

        $subjectIdentities = GeneralUtility::makeInstance(SubjectIdentityProvider::class)
            ->getSubjectIdentities($GLOBALS['BE_USER']);

        $permissionList = GeneralUtility::makeInstance(PermissionProvider::class)
            ->findList(new ObjectIdentity(sprintf('table/%s', $table)), $subjectIdentities);

        $this->assertEquals($granted, $permissionList->isGranted($masks, $subjectIdentities));
    }

    public function checkTableFieldPermissionsProvider()
    {
        return [
            [2, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'tt_content', 'header'],
            [2, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'pages', 'title'],
            [2, false, [TablePermissionRetrivalStrategy::PERMISSION_READ, TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'pages', 'editlock'],
            [1, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'be_users', 'admin'],
            [1, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'be_groups', 'workspace_perms'],
            [1, true, [TablePermissionRetrivalStrategy::PERMISSION_READ, TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'sys_file_reference', 'description'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'be_users', 'email'],
            [3, false, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'tt_content', 'colPos'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'pages', 'url'],
            [3, false, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'pages', 'slug'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE, TablePermissionRetrivalStrategy::PERMISSION_READ], 'sys_language', 'title'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'pages', 'title'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_READ], 'pages', 'slug'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'tt_content', 'header'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE], 'tt_content', 'header_layout'],
            [3, true, [TablePermissionRetrivalStrategy::PERMISSION_WRITE, TablePermissionRetrivalStrategy::PERMISSION_READ], 'tt_content', 'header'],
        ];
    }

    /**
     * @test
     * @dataProvider checkTableFieldPermissionsProvider
     */
    public function checkTableFieldPermissions(int $backendUser, bool $granted, array $masks, string $table, string $field)
    {
        $this->setUpBackendUserFromFixture($backendUser);

        $subjectIdentities = GeneralUtility::makeInstance(SubjectIdentityProvider::class)
            ->getSubjectIdentities($GLOBALS['BE_USER']);

        $permissionList = GeneralUtility::makeInstance(PermissionProvider::class)
            ->findList(new ObjectIdentity(sprintf('table/%s', $table)), $subjectIdentities);

        $this->assertEquals($granted, $permissionList->isGranted($masks, $subjectIdentities, new ObjectIdentity(sprintf('table/%s/field/%s', $table, $field))));
    }
}
