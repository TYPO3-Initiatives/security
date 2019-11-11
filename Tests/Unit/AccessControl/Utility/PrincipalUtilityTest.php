<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Tests\Unit\AccessControl\Attribute;

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

use TYPO3\CMS\Security\AccessControl\Attribute\PrincipalAttribute;
use TYPO3\CMS\Security\AccessControl\Utility\PrincipalUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class PrincipalUtilityTest extends UnitTestCase
{

    public function filterListProvider()
    {
        return [
            [
                [
                    new PrincipalAttribute('foo'),
                    new PrincipalAttribute('bar'),
                    new PrincipalAttribute('baz'),
                ],
                static function (PrincipalAttribute $principalAttribute) {
                    return $principalAttribute->getIdentifier() !== 'bar';
                },
                [
                    'cms:security:principal:foo' => new PrincipalAttribute('foo'),
                    'cms:security:principal:baz' => new PrincipalAttribute('baz'),
                ]
            ],
        ];
    }

    /**
     * @test
     * @dataProvider filterListProvider
     */
    public function filterList(array $principals, callable $predicate, array $expected)
    {
        $this->assertEquals(PrincipalUtility::filterList($principals, $predicate), $expected);
    }
}
