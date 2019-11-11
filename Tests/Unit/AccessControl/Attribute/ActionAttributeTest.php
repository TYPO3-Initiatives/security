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

use TYPO3\CMS\Security\AccessControl\Attribute\ActionAttribute;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class ActionAttributeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function constructPropagatesNamespaceAsIdentifier()
    {
        $subject = $this->getMockForAbstractClass(
            ActionAttribute::class, 
            [], 
            'ActionAttribute'
        );

        $this->assertEquals($subject->namespace, $subject->identifier);
    }

    /**
     * @test
     */
    public function constructPropagatesNamespaceAsName()
    {
        $subject = $this->getMockForAbstractClass(
            ActionAttribute::class, 
            [], 
            'ActionAttribute'
        );

        $this->assertEquals($subject->namespace, $subject->name);
    }
}
