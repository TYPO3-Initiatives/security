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
use TYPO3\CMS\Security\AccessControl\Policy\PolicySet;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class PolicySetTest extends UnitTestCase
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

        new PolicySet(
            '',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );
    }

    /**
     * @test
     */
    public function constructThrowsWhenPoliciesAreEmpty()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicySet(
            'qux',
            [],
            $this->evaluatorProphecy->reveal()
        );
    }

    /**
     * @test
     */
    public function constructThrowsWhenPoliciesContainInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);

        new PolicySet(
            'qux',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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

        new PolicySet(
            'baz',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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

        new PolicySet(
            'bar',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'qux',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'baz',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'qux',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal(),
            null,
            null,
            15
        );

        $this->assertSame(15, $subject->getPriority());
    }

    /**
     * @test
     */
    public function getDenyObligationsReturnsEmptyArrayIfNotSetOnConstruct()
    {
        $subject = new PolicySet(
            'bar',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'baz',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'bar',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $subject = new PolicySet(
            'bar',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
    public function getPoliciesReturnsGivenOneOnConstruct()
    {
        $subject = new PolicySet(
            'baz',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
                new Policy(
                    'bar',
                    [
                        new PolicyRule('baz'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals(
            [
                'qux' => new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
                'bar' => new Policy(
                    'bar',
                    [
                        new PolicyRule('baz'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $subject->getPolicies()
        );
    }

    /**
     * @test
     */
    public function getIteratorReturnsPoliciesArray()
    {
        $subject = new PolicySet(
            'baz',
            [
                new Policy(
                    'foo',
                    [
                        new PolicyRule('baz'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals(
            [
                'foo' => new Policy(
                    'foo',
                    [
                        new PolicyRule('baz'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $subject->getPolicies()
        );
    }

    /**
     * @test
     */
    public function offsetSetThrowsOnCall()
    {
        $this->expectException(NotSupportedMethodException::class);

        $subject = new PolicySet(
            'qux',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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

        $subject = new PolicySet(
            'bar',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );

        unset($subject['qux']);
    }

    /**
     * @test
     */
    public function offsetExistReturnsTrueWhenPolicyWithGivenIdExist()
    {
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'qux',
                    [
                        new PolicyRule('foo'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertTrue(isset($subject['qux']));
    }

    /**
     * @test
     */
    public function offsetExistReturnsFalseWhenPolicyWithGivenIdDoesNotExist()
    {
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'bar',
                    [
                        new PolicyRule('baz'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertFalse(isset($subject['qux']));
    }

    /**
     * @test
     */
    public function offsetGetReturnsPolicyRuleWithGivenId()
    {
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'baz',
                    [
                        new PolicyRule('bar'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
            ],
            $this->evaluatorProphecy->reveal()
        );

        $this->assertEquals(
            new Policy(
                'baz',
                [
                    new PolicyRule('bar'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
            $subject['baz']
        );
    }

    /**
     * @test
     */
    public function offsetGetReturnsNullWhenGivenIdDoesNotExist()
    {
        $subject = new PolicySet(
            'foo',
            [
                new Policy(
                    'baz',
                    [
                        new PolicyRule('bar'),
                    ],
                    $this->evaluatorProphecy->reveal()
                ),
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
        $policies = [
            new Policy(
                'foo',
                [
                    new PolicyRule('bar'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $resolverProphecy = $this->prophesize(Resolver::class);
        $resolverProphecy->evaluate('false')->willReturn(false);
        $resolverMock = $resolverProphecy->reveal();

        $subject = new PolicySet(
            'qux',
            $policies,
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
        $policies = [
            new Policy(
                'baz',
                [
                    new PolicyRule('bar'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->shouldBeCalled()->willReturn(
            new PolicyDecision(PolicyDecision::NOT_APPLICABLE)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet(
            'bar',
            $policies,
            $evaluatorMock
        );

        $subject->evaluate($this->resolverStub);

        $evaluatorProphecy->checkProphecyMethodsPredictions();
    }

    /**
     * @test
     */
    public function evaluateReturnsNonApplicableDecisionIfEvaluatorAlsoDoes()
    {
        $policies = [
            new Policy(
                'foo',
                [
                    new PolicyRule('bar'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(
            new PolicyDecision(PolicyDecision::NOT_APPLICABLE)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet(
            'qux',
            $policies,
            $evaluatorMock
        );

        $this->assertEquals(new PolicyDecision(PolicyDecision::NOT_APPLICABLE), $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsPermitDecisionIfEvaluatorAlsoDoes()
    {
        $policies = [
            new Policy(
                'bar',
                [
                    new PolicyRule('foo'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT)
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet('qux', $policies, $evaluatorMock);

        $this->assertEquals(new PolicyDecision(PolicyDecision::PERMIT), $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsDenyDecisionIfEvaluatorAlsoDoes()
    {
        $policies = [
            new Policy(
                'qux',
                [
                    new PolicyRule('bar'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(new PolicyDecision(PolicyDecision::DENY));
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet('baz', $policies, $evaluatorMock);

        $this->assertEquals(new PolicyDecision(PolicyDecision::DENY), $subject->evaluate($this->resolverStub));
    }

    /**
     * @test
     */
    public function evaluateReturnsMergedDenyDecision()
    {
        $policies = [
            new Policy(
                'baz',
                [
                    new PolicyRule('foo'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
            new Policy(
                'bar',
                [
                    new PolicyRule('qux'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(
            new PolicyDecision(PolicyDecision::DENY, null, new PolicyObligation('qux'), new PolicyObligation('bar'))
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet(
            'qux',
            $policies,
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
                null,
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
        $policies = [
            new Policy(
                'qux',
                [
                    new PolicyRule('foo'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
            new Policy(
                'baz',
                [
                    new PolicyRule('bar'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT, null, new PolicyObligation('foo'), new PolicyObligation('bar'))
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet(
            'qux',
            $policies,
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
                null,
                new PolicyObligation('foo'),
                new PolicyObligation('bar'),
                new PolicyObligation('foo'),
                new PolicyObligation('baz')
            ),
            $subject->evaluate($this->resolverStub)
        );
    }

    /**
     * @test
     */
    public function evaluateReturnsDeterminingRule()
    {
        $policies = [
            new Policy(
                'bar',
                [
                    new PolicyRule('foo'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
            new Policy(
                'baz',
                [
                    new PolicyRule('qux'),
                ],
                $this->evaluatorProphecy->reveal()
            ),
        ];

        $evaluatorProphecy = $this->prophesize(EvaluatorInterface::class);
        $evaluatorProphecy->process($this->resolverStub, ...$policies)->willReturn(
            new PolicyDecision(PolicyDecision::PERMIT, $policies[1][0])
        );
        $evaluatorMock = $evaluatorProphecy->reveal();

        $subject = new PolicySet(
            'qux',
            $policies,
            $evaluatorMock,
            null,
            null,
            null
        );

        $this->assertEquals(
            new PolicyDecision(
                PolicyDecision::PERMIT,
                $policies[1][0]
            ),
            $subject->evaluate($this->resolverStub)
        );
    }
}
