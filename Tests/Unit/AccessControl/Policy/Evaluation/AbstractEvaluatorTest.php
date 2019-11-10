<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Tests\Unit\AccessControl\Policy\Evaluation;

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

use Prophecy\Argument;
use TYPO3\CMS\Security\AccessControl\Policy\Evaluation\EvaluableInterface;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyObligation;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
abstract class AbstractEvaluatorTest extends UnitTestCase
{
    protected function buildEvaluables(array $data)
    {
        return array_map(function ($data) {
            $stub = $this->prophesize(EvaluableInterface::class);
            $stub->evaluate(Argument::any())->willReturn(
                new PolicyDecision(
                    $data[0],
                    ...array_map(function ($data) {
                        return new PolicyObligation($data[0]);
                    }, $data[1] ?? [])
                )
            );

            if (isset($data[2])) {
                $stub->getPriority(Argument::any())->willReturn($data[2]);
            }

            return $stub->reveal();
        }, $data);
    }
}
