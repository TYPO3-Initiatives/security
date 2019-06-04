<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Core\Tests\Functional\GraphQL;

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

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3\CMS\Security\Permission\SubjectIdentityProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Permission\PermissionProvider;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\TablePermissionRetrivalStrategy;

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

    protected function setUp()
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
}
