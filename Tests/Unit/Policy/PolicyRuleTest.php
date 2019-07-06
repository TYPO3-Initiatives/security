<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Tests\Unit\Policy;

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
use Prophecy\Argument;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Security\Policy\PolicyDecision;
use TYPO3\CMS\Security\Policy\PolicyObligation;
use TYPO3\CMS\Security\Policy\PolicyRule;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class PolicyRuleTest extends UnitTestCase
{
    protected $resolverStub;

    public function setUp(): void
    {
        $resolverProphecy = $this->prophesize(Resolver::class);

        $resolverProphecy->evaluate(Argument::exact('true'))->willReturn(true);
        $resolverProphecy->evaluate(Argument::exact('false'))->willReturn(false);

        $this->resolverStub = $resolverProphecy->reveal();
    }

    /**
     * @test
     */
    public function constructThrowsWhenIdIsEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicyRule('');
    }

    /**
     * @test
     */
    public function constructThrowsWhenEffectIsNotDenyOrPermit()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicyRule('foo', null, null, 'baz');
    }

    /**
     * @test
     */
    public function constructThrowsWhenDenyObligationsContainInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicyRule('foo', null, null, null, null, [1,2,3]);
    }

    /**
     * @test
     */
    public function constructThrowsWhenPermitObligationsContainInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicyRule('foo', null, null, null, null, null, [1,2,3]);
    }

    /**
     * @test
     */
    public function getPriorityReturnsOneIfNotSetOnConstruct()
    {
        $subject = new PolicyRule('foo');

        $this->assertSame(1, $subject->getPriority());
    }

    /**
     * @test
     */
    public function getPriorityReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('foo', null, null, null, 4711);

        $this->assertSame(4711, $subject->getPriority());
    }

    /**
     * @test
     */
    public function getEffectReturnsDenyIfNotSetOnConstruct()
    {
        $subject = new PolicyRule('bar');

        $this->assertSame(PolicyRule::EFFECT_DENY, $subject->getEffect());
    }

    /**
     * @test
     */
    public function getEffectReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('bar', null, null, PolicyRule::EFFECT_PERMIT);

        $this->assertSame(PolicyRule::EFFECT_PERMIT, $subject->getEffect());
    }

    /**
     * @test
     */
    public function getIdReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('baz');

        $this->assertSame('baz', $subject->getId());
    }

    /**
     * @test
     */
    public function getTargetReturnsNullIfNotSetOnConstruct()
    {
        $subject = new PolicyRule('bar');

        $this->assertSame(null, $subject->getTarget());
    }

    /**
     * @test
     */
    public function getTargetReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('bar', 'true');

        $this->assertSame('true', $subject->getTarget());
    }

    /**
     * @test
     */
    public function getConditionReturnsNullIfNotSetOnConstruct()
    {
        $subject = new PolicyRule('qux');

        $this->assertSame(null, $subject->getCondition());
    }

    /**
     * @test
     */
    public function getConditionReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('bar', null, 'false');

        $this->assertSame('false', $subject->getCondition());
    }

    /**
     * @test
     */
    public function getDenyObligationsReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('baz', null, null, null, null, [new PolicyObligation('bar'), new PolicyObligation('qux')]);

        $this->assertEquals([new PolicyObligation('bar'), new PolicyObligation('qux')], $subject->getDenyObligations());
    }

    /**
     * @test
     */
    public function getPermitObligationsReturnsGivenOneOnConstruct()
    {
        $subject = new PolicyRule('qux', null, null, null, null, null, [new PolicyObligation('foo', [1,2,'bar'])]);

        $this->assertEquals([new PolicyObligation('foo', [1,2,'bar'])], $subject->getPermitObligations());
    }

    /**
     * @test
     */
    public function evaluateReturnsApplicableDecisionWhenTargetAndConditionIsNull()
    {
        $subject = new PolicyRule('foo', null, null, PolicyRule::EFFECT_DENY);
        $expected = new PolicyDecision(PolicyDecision::DENY);

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsNonApplicableDecisionWhenTargetEvaluatesToFalse()
    {
        $subject = new PolicyRule('qux', 'false');
        $expected = new PolicyDecision(PolicyDecision::NOT_APPLICABLE);

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsApplicableDecisionWhenTargetEvaluatesToTrueAndConditionIsNotSet()
    {
        $subject = new PolicyRule('baz', 'true', null, PolicyRule::EFFECT_PERMIT);
        $expected = new PolicyDecision(PolicyDecision::PERMIT);

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsApplicableDecisionWhenTargetAndConditionEvaluatesToTrue()
    {
        $subject = new PolicyRule('foo', 'true', 'true', PolicyRule::EFFECT_DENY);
        $expected = new PolicyDecision(PolicyDecision::DENY);

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsDenyDecisionIfApplicableAndEffectIsNotSetOnConstruct()
    {
        $subject = new PolicyRule('qux');
        $expected = new PolicyDecision(PolicyDecision::DENY);

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsDecisionWithPermitObligationsOnPermit()
    {
        $subject = new PolicyRule(
            'bar',
            null,
            null,
            PolicyRule::EFFECT_PERMIT,
            null,
            [new PolicyObligation('foo')],
            [new PolicyObligation('baz'), new PolicyObligation('bar')]
        );
        $expected = new PolicyDecision(PolicyDecision::PERMIT, new PolicyObligation('baz'), new PolicyObligation('bar'));

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsDecisionWithDenyObligationsOnPermit()
    {
        $subject = new PolicyRule(
            'bar',
            null,
            null,
            PolicyRule::EFFECT_DENY,
            null,
            [new PolicyObligation('foo'), new PolicyObligation('qux')],
            [new PolicyObligation('baz')]
        );
        $expected = new PolicyDecision(PolicyDecision::DENY, new PolicyObligation('foo'), new PolicyObligation('qux'));

        $this->assertEquals($expected, $subject->evaluate($this->resolverStub));
    }
}
