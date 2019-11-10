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
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Security\AccessControl\Policy\Evaluation\EvaluatorInterface;
use TYPO3\CMS\Security\AccessControl\Policy\Exception\NotSupportedMethodException;
use TYPO3\CMS\Security\AccessControl\Policy\Policy;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyObligation;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyRule;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class PolicyTest extends UnitTestCase
{
    /**
     * @var Resolver
     */
    protected $resolverStub;

    /**
     * @var ObjectProphecy
     */
    protected $evaluatorProphecy;

    public function setUp(): void
    {
        $this->resolverStub = $this->prophesize(Resolver::class)->reveal();
        $this->evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
    }

    /**
     * @test
     */
    public function constructThrowsWhenIdIsEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        new Policy(
            '',
            [
                new PolicyRule('qux'),
            ],
            $this->evaluatorProphecy->reveal()
        );
    }

    /**
     * @test
     */
    public function constructThrowsWhenRulesAreEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        new Policy(
            'qux',
            [],
            $this->evaluatorProphecy->reveal()
        );
    }

    /**
     * @test
     */
    public function constructThrowsWhenRulesContainInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        new Policy(
            'qux',
            [
                new PolicyRule('foo'),
                'bar',
            ],
            $this->evaluatorProphecy->reveal()
        );
    }

    /**
     * @test
     */
    public function constructThrowsWhenDenyObligationsContainInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        new Policy(
            'baz',
            [
                new PolicyRule('bar'),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            null,
            null,
            [
                new PolicyObligation('qux'),
                'foo',
            ]
        );
    }

    /**
     * @test
     */
    public function constructThrowsWhenPermitObligationsContainInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        new Policy(
            'bar',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            null,
            null,
            null,
            [
                new PolicyObligation('foo'),
                'qux',
            ]
        );
    }

    /**
     * @test
     */
    public function getIdReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'qux',
            [
                new PolicyRule('foo'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertSame('qux', $subject->getId());
    }

    /**
     * @test
     */
    public function getDescriptionReturnsNullIfNotSetOnConstruct()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertSame(null, $subject->getDescription());
    }

    /**
     * @test
     */
    public function getDescriptionReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal(),
            'bar'
        );

        $this->assertSame('bar', $subject->getDescription());
    }

    /**
     * @test
     */
    public function getTargetReturnsNullIfNotSetOnConstruct()
    {
        $subject = new Policy(
            'baz',
            [
                new PolicyRule('qux'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertNull($subject->getTarget());
    }

    /**
     * @test
     */
    public function getTargetReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'qux',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            'foo'
        );

        $this->assertSame('foo', $subject->getTarget());
    }

    /**
     * @test
     */
    public function getPriorityReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('bar'),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            null,
            51
        );

        $this->assertSame(51, $subject->getPriority());
    }

    /**
     * @test
     */
    public function getDenyObligationsReturnsEmptyArrayIfNotSetOnConstruct()
    {
        $subject = new Policy(
            'bar',
            [
                new PolicyRule('qux'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals([], $subject->getDenyObligations());
    }

    /**
     * @test
     */
    public function getDenyObligationsReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'baz',
            [
                new PolicyRule('bar'),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            null,
            null,
            [new PolicyObligation('bar'), new PolicyObligation('qux')]
        );

        $this->assertEquals([new PolicyObligation('bar'), new PolicyObligation('qux')], $subject->getDenyObligations());
    }

    /**
     * @test
     */
    public function getPermitObligationsReturnsEmptyArrayIfNotSetOnConstruct()
    {
        $subject = new Policy(
            'bar',
            [
                new PolicyRule('qux'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals([], $subject->getDenyObligations());
    }

    /**
     * @test
     */
    public function getPermitObligationsReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'bar',
            [
                new PolicyRule('bar'),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            null,
            null,
            null,
            [new PolicyObligation('baz'), new PolicyObligation('qux')]
        );

        $this->assertEquals([new PolicyObligation('baz'), new PolicyObligation('qux')], $subject->getPermitObligations());
    }

    /**
     * @test
     */
    public function getRulesReturnsGivenOneOnConstruct()
    {
        $subject = new Policy(
            'baz',
            [
                new PolicyRule('qux'),
                new PolicyRule('bar'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals(
            ['qux' => new PolicyRule('qux'), 'bar' => new PolicyRule('bar')],
            $subject->getRules()
        );
    }

    /**
     * @test
     */
    public function getIteratorReturnsRulesArray()
    {
        $subject = new Policy(
            'qux',
            [
                new PolicyRule('foo'),
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals(
            ['foo' => new PolicyRule('foo'), 'baz' => new PolicyRule('baz')],
            $subject->getIterator()
        );
    }

    /**
     * @test
     */
    public function offsetSetThrowsOnCall()
    {
        $this->expectException(NotSupportedMethodException::class);

        $subject = new Policy(
            'qux',
            [
                new PolicyRule('foo'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $subject['foo'] = new PolicyRule('baz');
    }

    /**
     * @test
     */
    public function offsetUnsetThrowsOnCall()
    {
        $this->expectException(NotSupportedMethodException::class);

        $subject = new Policy(
            'bar',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        unset($subject['qux']);
    }

    /**
     * @test
     */
    public function offsetExistReturnsTrueWhenRuleWithGivenIdExist()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('qux'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertTrue(isset($subject['qux']));
    }

    /**
     * @test
     */
    public function offsetExistReturnsFalseWhenRuleWithGivenIdDoesNotExist()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('bar'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertFalse(isset($subject['foo']));
    }

    /**
     * @test
     */
    public function offsetGetReturnsPolicyRuleWithGivenId()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals(new PolicyRule('baz'), $subject['baz']);
    }

    /**
     * @test
     */
    public function offsetGetReturnsNullWhenGivenIdDoesNotExist()
    {
        $subject = new Policy(
            'foo',
            [
                new PolicyRule('baz'),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertNull($subject['qux']);
    }

    /**
     * @test
     */
    public function evaluateReturnsNotApplicableDecisionIfTargetDoesNotMatch()
    {
        $rules = [
            new PolicyRule('baz'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->evaluate('false')->willReturn(false);
        $resolverMock = $resolverProphecy->reveal();

        $subject = new Policy(
            'qux',
            $rules,
            $evaluatorMock,
            null,
            'false'
        );

        $this->assertEquals(new PolicyDecision(PolicyDecision::NOT_APPLICABLE), $subject->evaluate($resolverMock));
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function evaluateUsesEvaluatorGivenOnConstruct()
    {
        $rules = [
            new PolicyRule('baz'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->shouldBeCalled()->willReturn(
            new PolicyDecision(PolicyDecision::NOT_APPLICABLE)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new Policy(
            'bar',
            $rules,
            $evaluatorMock
        );

        $subject->evaluate($this->resolverStub);

        $evaluatorProphecy->checkProphecyMethodsPredictions();
    }

    /**
     * @test
     */
    public function evaluateReturnsNotApplicableDecisionIfEvaluatorAlsoDoes()
    {
        $rules = [
            new PolicyRule('baz'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->willReturn(
            new PolicyDecision(PolicyDecision::NOT_APPLICABLE)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new Policy(
            'qux',
            $rules,
            $evaluatorMock
        );

        $this->assertEquals(new PolicyDecision(PolicyDecision::NOT_APPLICABLE), $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsPermitDecisionIfEvaluatorAlsoDoes()
    {
        $rules = [
            new PolicyRule('qux'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new Policy('qux', $rules, $evaluatorMock);

        $this->assertEquals(new PolicyDecision(PolicyDecision::PERMIT), $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsDenyDecisionIfEvaluatorAlsoDoes()
    {
        $rules = [
            new PolicyRule('bar'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->willReturn(
            new PolicyDecision(PolicyDecision::DENY)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new Policy('baz', $rules, $evaluatorMock);

        $this->assertEquals(new PolicyDecision(PolicyDecision::DENY), $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsMergedDenyDecision()
    {
        $rules = [
            new PolicyRule('foo'),
            new PolicyRule('baz'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->willReturn(
            new PolicyDecision(PolicyDecision::DENY, new PolicyObligation('qux'), new PolicyObligation('bar'))
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new Policy(
            'qux',
            $rules,
            $evaluatorMock,
            null,
            null,
            null,
            [new PolicyObligation('baz'), new PolicyObligation('foo')],
            [new PolicyObligation('bar')]
        );

        $this->assertEquals(
            new PolicyDecision(
                PolicyDecision::DENY,
                new PolicyObligation('qux'),
                new PolicyObligation('bar'),
                new PolicyObligation('baz'),
                new PolicyObligation('foo')
            ),
            $subject->evaluate($this->resolverStub)
        );
    }

    /**
     * @test
     */
    public function evaluateReturnsMergedPermitDecision()
    {
        $rules = [
            new PolicyRule('qux'),
            new PolicyRule('baz'),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$rules)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT, new PolicyObligation('foo'), new PolicyObligation('bar'))
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new Policy(
            'qux',
            $rules,
            $evaluatorMock,
            null,
            null,
            null,
            [new PolicyObligation('qux')],
            [new PolicyObligation('foo'), new PolicyObligation('baz')]
        );

        $this->assertEquals(
            new PolicyDecision(
                PolicyDecision::PERMIT,
                new PolicyObligation('foo'),
                new PolicyObligation('bar'),
                new PolicyObligation('foo'),
                new PolicyObligation('baz')
            ),
            $subject->evaluate($this->resolverStub)
        );
    }
}
