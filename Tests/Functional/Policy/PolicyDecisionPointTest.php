<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Tests\Functional\Policy;

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

use Example\AccessControl\Security\Attribute\ActionAttribute;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Attribute\ResourceAttribute;
use TYPO3\CMS\Security\Policy\PolicyDecision;
use TYPO3\CMS\Security\Policy\PolicyDecisionPoint;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Test case
 */
class PolicyDecisionPointTest extends FunctionalTestCase
{
    /**
     * @var array
     */
    protected $testExtensionsToLoad = [
        'typo3/sysext/security',
        'typo3/sysext/security/Tests/Functional/Fixtures/Extensions/test_access_control',
    ];

    /**
     * Sets up this test case.
     */
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function evaluateRequestProvider()
    {
        return [
            [
                [
                    'resource' => new ResourceAttribute('foo'),
                    'action' => new ActionAttribute(),
                ],
                '',
                PolicyDecision::PERMIT,
            ],
            [
                [
                    'resource' => new ResourceAttribute('bar'),
                    'action' => new ActionAttribute(),
                ],
                '',
                PolicyDecision::DENY,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider evaluateRequestProvider
     */
    public function evaluateRequest(array $attributes, string $policy, int $decision)
    {
        $subject = GeneralUtility::makeInstance(PolicyDecisionPoint::class);

        $this->assertSame($decision, $subject->authorize($attributes, $policy)->getValue());
    }
}
