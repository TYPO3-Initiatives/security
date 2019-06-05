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
use TYPO3\CMS\Security\Permission\AbstractSubjectIdentity;
use TYPO3\CMS\Security\Permission\PermissionList;
use TYPO3\CMS\Security\Permission\ObjectIdentity;
use TYPO3\CMS\Security\Permission\PermissionGrantingStrategy;
use TYPO3\CMS\Security\Permission\PermissionEntry;
use TYPO3\CMS\Security\Permission\Exception\NoPermissionEntryFoundException;
use TYPO3\CMS\Security\Permission\PermissionFieldEntry;

/**
 * Testcase for the TYPO3\CMS\Security\Permission\PermissionGrantingStrategy
 */
class PermissionGrantingStrategyTest extends UnitTestCase
{
    protected $subjectIdentityFactory;

    protected function setUp(): void
    {
        $this->subjectIdentityFactory = new class('mock') extends AbstractSubjectIdentity {
            public static function create(string $identifer) {
                return new self($identifer);
            }
        };
    }

    /**
     * @test
     */
    public function isGrantedFavorsLocalEntriesOverParentEntries()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');

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
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');
        $anotherSubjectIdentity = $this->subjectIdentityFactory::create('baz');

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
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $strategy->isGranted($permissionList, [1], [$subjectIdentity]);
    }

    /**
     * @test
     */
    public function isGrantedUsesFirstApplicableEntryToMakeUltimateDecisionForPermissionIdentityCombination()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');
        $anotherSubjectIdentity = $this->subjectIdentityFactory::create('baz');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $permissionList->add(new PermissionEntry(1, $anotherSubjectIdentity, 100, PermissionGrantingStrategy::ALL, true));
        $permissionList->add(new PermissionEntry(1, $subjectIdentity, 10, PermissionGrantingStrategy::ALL, false));
        $permissionList->add(new PermissionEntry(1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $this->assertFalse($strategy->isGranted($permissionList, [1], [$subjectIdentity, $anotherSubjectIdentity]));
    }

    /**
     * @test
     */
    public function isGrantedFavorsLocalFieldEntriesOverParentEntries()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);
        $permissionList->add(new PermissionFieldEntry(new ObjectIdentity('baz'), 1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $permissionList->setParent(new PermissionList(new ObjectIdentity('bar'), $strategy));
        $permissionList->getParent()->add(new PermissionEntry(1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, false));

        $this->assertTrue($strategy->isGranted($permissionList, [1], [$subjectIdentity], new ObjectIdentity('baz')));
    }

    /**
     * @test
     */
    public function isGrantedFallsBackToParentFieldEntriesIfNoLocalEntriesAreApplicable()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');
        $anotherSubjectIdentity = $this->subjectIdentityFactory::create('baz');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy, true);
        $permissionList->add(new PermissionFieldEntry(new ObjectIdentity('qux'), 1, $anotherSubjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $permissionList->setParent(new PermissionList(new ObjectIdentity('bar'), $strategy));
        $permissionList->getParent()->add(new PermissionFieldEntry(new ObjectIdentity('qux'), 1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $this->assertTrue($strategy->isGranted($permissionList, [1], [$subjectIdentity], new ObjectIdentity('qux')));
    }

    /**
     * @test
     */
    public function isGrantedThrowsExceptionIfNoFieldEntryIsFound()
    {
        $this->expectException(NoPermissionEntryFoundException::class);

        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $strategy->isGranted($permissionList, [1], [$subjectIdentity], new ObjectIdentity('baz'));
    }

    /**
     * @test
     */
    public function isGrantedUsesFirstApplicableFieldEntryToMakeUltimateDecisionForPermissionIdentityCombination()
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');
        $anotherSubjectIdentity = $this->subjectIdentityFactory::create('baz');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        $permissionList->add(new PermissionFieldEntry(new ObjectIdentity('qux'), 1, $anotherSubjectIdentity, 100, PermissionGrantingStrategy::ALL, true));
        $permissionList->add(new PermissionFieldEntry(new ObjectIdentity('qux'), 1, $subjectIdentity, 10, PermissionGrantingStrategy::ALL, false));
        $permissionList->add(new PermissionFieldEntry(new ObjectIdentity('qux'), 1, $subjectIdentity, 1, PermissionGrantingStrategy::ALL, true));

        $this->assertFalse($strategy->isGranted($permissionList, [1], [$subjectIdentity, $anotherSubjectIdentity], new ObjectIdentity('qux')));
    }

    /**
     * @test
     * @dataProvider isGrantedSupportsDifferentStrategiesDataProvider
     */
    public function isGrantedSupportsDifferentStrategies(string $maskStrategy, int $mask, int $requiredMask, ?string $field, bool $result)
    {
        $strategy = new PermissionGrantingStrategy();
        $subjectIdentity = $this->subjectIdentityFactory::create('foo');

        $permissionList = new PermissionList(new ObjectIdentity('bar'), $strategy);

        if ($field) {
            $permissionList->add(new PermissionFieldEntry(new ObjectIdentity($field), $mask, $subjectIdentity, 1, $maskStrategy, true));
        } else {
            $permissionList->add(new PermissionEntry($mask, $subjectIdentity, 1, $maskStrategy, true));
        }

        if (!$result) {
            $this->expectException(NoPermissionEntryFoundException::class);
        }

        if ($field) {
            $this->assertTrue($strategy->isGranted($permissionList, [$requiredMask], [$subjectIdentity], new ObjectIdentity($field)));
        } else {
            $this->assertTrue($strategy->isGranted($permissionList, [$requiredMask], [$subjectIdentity]));
        }
    }

    public function isGrantedSupportsDifferentStrategiesDataProvider()
    {
        return [
            ['all', 1 << 0 | 1 << 1, 1 << 0, null, true],
            ['all', 1 << 0 | 1 << 1, 1 << 2, null, false],
            ['all', 1 << 0 | 1 << 10, 1 << 0 | 1 << 10, null, true],
            ['all', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1 | 1 << 2, null, false],
            ['any', 1 << 0 | 1 << 1, 1 << 0, null, true],
            ['any', 1 << 0 | 1 << 1, 1 << 0 | 1 << 2, null, true],
            ['any', 1 << 0 | 1 << 1, 1 << 2, null, false],
            ['equal', 1 << 0 | 1 << 1, 1 << 0, null, false],
            ['equal', 1 << 0 | 1 << 1, 1 << 1, null, false],
            ['equal', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1, null, true],
            ['all', 1 << 0 | 1 << 1, 1 << 0, 'baz', true],
            ['all', 1 << 0 | 1 << 1, 1 << 2, 'baz', false],
            ['all', 1 << 0 | 1 << 10, 1 << 0 | 1 << 10, 'baz', true],
            ['all', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1 | 1 << 2, 'baz', false],
            ['any', 1 << 0 | 1 << 1, 1 << 0, 'baz', true],
            ['any', 1 << 0 | 1 << 1, 1 << 0 | 1 << 2, 'baz', true],
            ['any', 1 << 0 | 1 << 1, 1 << 2, 'baz', false],
            ['equal', 1 << 0 | 1 << 1, 1 << 0, 'baz', false],
            ['equal', 1 << 0 | 1 << 1, 1 << 1, 'baz', false],
            ['equal', 1 << 0 | 1 << 1, 1 << 0 | 1 << 1, 'baz', true],
        ];
    }
}