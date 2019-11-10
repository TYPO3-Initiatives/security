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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Policy\AbstractPolicy;
use TYPO3\CMS\Security\Policy\Evaluation\DenyOverridesEvaluator;
use TYPO3\CMS\Security\Policy\Evaluation\FirstApplicableEvaluator;
use TYPO3\CMS\Security\Policy\Evaluation\HighestPriorityEvaluator;
use TYPO3\CMS\Security\Policy\Evaluation\PermitOverridesEvaluator;
use TYPO3\CMS\Security\Policy\Policy;
use TYPO3\CMS\Security\Policy\PolicyFactory;
use TYPO3\CMS\Security\Policy\PolicyObligation;
use TYPO3\CMS\Security\Policy\PolicyRule;
use TYPO3\CMS\Security\Policy\PolicySet;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class PolicyFactoryTest extends UnitTestCase
{
    public function validConfigurationProvider()
    {
        return [
            [
                [
                    'description' => 'bar',
                    'target' => 'foo or bar',
                    'algorithm' => 'denyOverrides',
                    'priority' => 50,
                    'rules' => [
                        'qux' => [
                            'target' => 'bar',
                            'condition' => 'baz',
                        ],
                    ],
                ],
                'foo',
                new Policy(
                    'foo',
                    [
                        'qux' => new PolicyRule(
                            'foo\qux',
                            'bar',
                            'baz',
                            null,
                            null,
                            null,
                            null
                        ),
                    ],
                    new DenyOverridesEvaluator(),
                    'bar',
                    'foo or bar',
                    50,
                    null,
                    null
                ),
            ],
            [
                [
                    'target' => 'true',
                    'description' => 'baz',
                    'algorithm' => 'highestPriority',
                    'policies' => [
                        'foo' => [
                            'target' => 'true',
                            'description' => 'foo',
                            'algorithm' => 'highestPriority',
                            'priority' => 20,
                            'rules' => [
                                [
                                    'target' => 'false',
                                    'effect' => 'deny',
                                    'priority' => 10,
                                    'obligation' => [
                                        'deny' => [
                                            'baz' => ['qux'],
                                        ],
                                    ],
                                ],
                                [
                                    'condition' => 'true',
                                    'effect' => 'permit',
                                    'priority' => 20,
                                    'obligation' => [
                                        'permit' => [
                                            'foo' => ['bar'],
                                            'baz' => ['bar', 'qux'],
                                        ],
                                        'deny' => [
                                            'bar' => ['foo'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'bar' => [
                            'target' => 'true',
                            'description' => 'bar',
                            'algorithm' => 'permitOverrides',
                            'obligation' => [
                                'permit' => [
                                    'foo' => [],
                                    'bar' => ['baz', 'qux'],
                                ],
                            ],
                            'policies' => [
                                'baz' => [
                                    'target' => 'true',
                                    'algorithm' => 'denyOverrides',
                                    'rules' => [
                                        [
                                            'condition' => 'true',
                                        ],
                                        [
                                            'condition' => 'true',
                                            'effect' => 'permit',
                                        ],
                                    ],
                                ],
                                'qux' => [
                                    'target' => 'true',
                                    'rules' => [
                                        'baz' => [
                                            'target' => 'false',
                                            'condition' => null,
                                            'effect' => 'permit',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                null,
                new PolicySet(
                    'Root',
                    [
                        'foo' => new Policy(
                            'Root\foo',
                            [
                                new PolicyRule(
                                    'Root\foo\0',
                                    'false',
                                    null,
                                    PolicyRule::EFFECT_DENY,
                                    10,
                                    [
                                        new PolicyObligation('baz', ['qux']),
                                    ],
                                    null
                                ),
                                new PolicyRule(
                                    'Root\foo\1',
                                    null,
                                    'true',
                                    PolicyRule::EFFECT_PERMIT,
                                    20,
                                    [
                                        new PolicyObligation('bar', ['foo']),
                                    ],
                                    [
                                        new PolicyObligation('foo', ['bar']),
                                        new PolicyObligation('baz', ['bar', 'qux']),
                                    ]
                                ),
                            ],
                            new HighestPriorityEvaluator(),
                            'foo',
                            'true',
                            20,
                            null,
                            null
                        ),
                        'bar' => new PolicySet(
                            'Root\bar',
                            [
                                'baz' =>new Policy(
                                    'Root\bar\baz',
                                    [
                                        new PolicyRule(
                                            'Root\bar\baz\0',
                                            null,
                                            'true',
                                            null,
                                            null,
                                            null,
                                            null
                                        ),
                                        new PolicyRule(
                                            'Root\bar\baz\1',
                                            null,
                                            'true',
                                            PolicyRule::EFFECT_PERMIT,
                                            null,
                                            null,
                                            null
                                        ),
                                    ],
                                    new DenyOverridesEvaluator(),
                                    null,
                                    'true',
                                    null,
                                    null,
                                    null
                                ),
                                'qux' => new Policy(
                                    'Root\bar\qux',
                                    [
                                        'baz' => new PolicyRule(
                                            'Root\bar\qux\baz',
                                            'false',
                                            null,
                                            PolicyRule::EFFECT_PERMIT,
                                            null,
                                            null,
                                            null
                                        ),
                                    ],
                                    new FirstApplicableEvaluator(),
                                    null,
                                    'true',
                                    null,
                                    null,
                                    null
                                ),
                            ],
                            new PermitOverridesEvaluator(),
                            'bar',
                            'true',
                            null,
                            null,
                            [
                                new PolicyObligation('foo', []),
                                new PolicyObligation('bar', ['baz', 'qux']),
                            ]
                        ),
                    ],
                    new HighestPriorityEvaluator(),
                    'baz',
                    'true',
                    null,
                    null,
                    null
                ),
            ],
        ];
    }

    /**
     * @test
     * @dataProvider validConfigurationProvider
     */
    public function buildValidConfiguration(array $configuration, ?string $name, AbstractPolicy $expected)
    {
        $this->assertEquals(
            $expected,
            GeneralUtility::makeInstance(PolicyFactory::class)->build($configuration, $name)
        );
    }

    public function invalidConfigurationProvider()
    {
        return [
            [
                [
                    'target' => 'foo or bar',
                    'rules' => [],
                ],
            ],
            [
                [
                    'target' => 'foo or bar',
                    'algorithm' => 'denOverrides',
                    'rules' => [
                        'qux' => [
                            'condition' => 'baz',
                        ],
                    ],
                ],
            ],
            [
                [
                    'algorithm' => 'denOverrides',
                    'rules' => [
                        'qux' => [
                            'condition' => 'baz',
                        ],
                    ],
                ],
            ],
            [
                [
                    'rules' => [
                        'qux' => [
                            'condition' => 'baz',
                        ],
                    ],
                    'policies' => [
                        [
                            'target' => 'foo or bar',
                            'rules' => [
                                'qux' => [
                                    'condition' => 'baz',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'policies' => [
                        [
                            'target' => 'foo or bar',
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @test
     * @dataProvider invalidConfigurationProvider
     */
    public function buildThrowsInvalidArgumentForInvalidConfiguration(array $configuration)
    {
        $this->expectException(InvalidArgumentException::class);
        GeneralUtility::makeInstance(PolicyFactory::class)->build($configuration);
    }
}
