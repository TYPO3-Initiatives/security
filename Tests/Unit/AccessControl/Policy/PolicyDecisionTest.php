<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Tests\Unit\AccessControl\Policy;

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

use InvalidArgumentException;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyObligation;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class PolicyDecisionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function constructThrowsWhenNonApplicableIsTriedToCreateWithObligations()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicyDecision(PolicyDecision::NOT_APPLICABLE, new PolicyObligation('foo'));
    }

    /**
     * @test
     */
    public function constructThrowsWhenAnInvaludValueIsUsed()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicyDecision(3, new PolicyObligation('foo'));
    }

    /**
     * @test
     */
    public function getValueReturnsPermitIfSetOnConstruct()
    {
        $subject = new PolicyDecision(PolicyDecision::PERMIT);
        $this->assertEquals(PolicyDecision::PERMIT, $subject->getValue());
    }

    /**
     * @test
     */
    public function getValueReturnsDenyIfSetOnConstruct()
    {
        $subject = new PolicyDecision(PolicyDecision::DENY);
        $this->assertEquals(PolicyDecision::DENY, $subject->getValue());
    }

    /**
     * @test
     */
    public function getValueReturnsNotApplicableIfSetOnConstruct()
    {
        $subject = new PolicyDecision(PolicyDecision::NOT_APPLICABLE);
        $this->assertEquals(PolicyDecision::NOT_APPLICABLE, $subject->getValue());
    }

    /**
     * @test
     */
    public function getObligationsReturnsEmptyArrayIfNoneGivenOnConstruct()
    {
        $subject = new PolicyDecision(PolicyDecision::PERMIT);
        $this->assertEmpty($subject->getObligations());
    }

    /**
     * @test
     */
    public function getObligationsReturnsGivenOnConstruct()
    {
        $subject = new PolicyDecision(PolicyDecision::PERMIT, new PolicyObligation('bar'), new PolicyObligation('baz'));
        $this->assertEquals([new PolicyObligation('bar'), new PolicyObligation('baz')], $subject->getObligations());
    }

    /**
     * @test
     */
    public function isApplicableReturnsTrueWhenDecisionIsTrue()
    {
        $subject = new PolicyDecision(PolicyDecision::PERMIT);
        $this->assertTrue($subject->isApplicable());
    }

    /**
     * @test
     */
    public function isApplicableReturnsTrueWhenDecisionIsFalse()
    {
        $subject = new PolicyDecision(PolicyDecision::DENY);
        $this->assertTrue($subject->isApplicable());
    }

    /**
     * @test
     */
    public function isApplicableReturnsFalseWhenDecisionIsNull()
    {
        $subject = new PolicyDecision(PolicyDecision::NOT_APPLICABLE);
        $this->assertFalse($subject->isApplicable());
    }

    /**
     * @test
     */
    public function addCreatesNewInstance()
    {
        $subject = new PolicyDecision(PolicyDecision::PERMIT);
        $this->assertNotSame($subject, $subject->add(new PolicyObligation('foo')));
    }

    /**
     * @test
     */
    public function addCreatesNewInstanceWithAllObligations()
    {
        $subject = new PolicyDecision(PolicyDecision::DENY, new PolicyObligation('bar'));
        $expected = new PolicyDecision(PolicyDecision::DENY, new PolicyObligation('bar'), new PolicyObligation('foo'));
        $this->assertEquals($expected, $subject->add(new PolicyObligation('foo')));
    }

    /**
     * @test
     */
    public function addThrowsWhenUsedOnNonApplicableDecision()
    {
        $this->expectException(InvalidArgumentException::class);

        $subject = new PolicyDecision(PolicyDecision::NOT_APPLICABLE);
        $subject->add(new PolicyObligation('foo'));
    }

    /**
     * @test
     */
    public function mergeCreatesNewInstance()
    {
        $subject = new PolicyDecision(PolicyDecision::DENY);
        $this->assertNotSame($subject, $subject->merge(new PolicyDecision(PolicyDecision::DENY)));
    }

    /**
     * @test
     */
    public function mergeCreatesNewInstanceWithAllObligations()
    {
        $subject = new PolicyDecision(PolicyDecision::PERMIT, new PolicyObligation('bar'));
        $expected = new PolicyDecision(PolicyDecision::PERMIT, new PolicyObligation('bar'), new PolicyObligation('foo'));
        $this->assertEquals($expected, $subject->merge(new PolicyDecision(PolicyDecision::PERMIT, new PolicyObligation('foo'))));
    }

    /**
     * @test
     */
    public function mergeThrowsWhenUsedOnNonApplicableDecision()
    {
        $this->expectException(InvalidArgumentException::class);

        $subject = new PolicyDecision(PolicyDecision::NOT_APPLICABLE);
        $subject->merge(new PolicyDecision(PolicyDecision::NOT_APPLICABLE));
    }

    /**
     * @test
     */
    public function mergeThrowsWhenBothDecisionsHaveNotTheSameResult()
    {
        $this->expectException(InvalidArgumentException::class);

        $subject = new PolicyDecision(PolicyDecision::PERMIT);
        $subject->merge(new PolicyDecision(PolicyDecision::DENY));
    }
}
