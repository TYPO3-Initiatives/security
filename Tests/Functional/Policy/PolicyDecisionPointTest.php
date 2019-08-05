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

use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\EntityResourceAttribute;
use TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute\ReadActionAttribute;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        'typo3/sysext/security/Tests/Functional/Fixtures/Extensions/security_example',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/../Fixtures/Permission.csv');
    }

    public function evaluateBackendUserRequestProvider()
    {
        return [
            [
                1,
                [
                    'resource' => new EntityResourceAttribute('be_users'),
                    'action' => new ReadActionAttribute(),
                ],
                '',
                PolicyDecision::PERMIT,
            ],
            [
                3,
                [
                    'resource' => new EntityResourceAttribute('tt_content'),
                    'action' => new ReadActionAttribute(),
                ],
                '',
                PolicyDecision::PERMIT,
            ],
        ];
    }

    /**
     * @test
     * @dataProvider evaluateBackendUserRequestProvider
     */
    public function evaluateBackendUserRequest(int $backendUser, array $attributes, string $policy, int $decision)
    {
        $this->setUpBackendUserFromFixture($backendUser);

        $subject = GeneralUtility::makeInstance(PolicyDecisionPoint::class);

        $this->assertSame($decision, $subject->authorize($attributes, $policy)->getValue());
    }
}
