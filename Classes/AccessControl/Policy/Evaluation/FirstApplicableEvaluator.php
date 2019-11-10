<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Policy\Evaluation;

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

use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;

/**
 * @internal
 */
class FirstApplicableEvaluator implements EvaluatorInterface
{
    public function process(Resolver $resolver, EvaluableInterface ...$evaluables): PolicyDecision
    {
        foreach ($evaluables as $evaluable) {
            $next = $evaluable->evaluate($resolver);

            if ($next->isApplicable()) {
                return $next;
            }
        }

        return new PolicyDecision(PolicyDecision::NOT_APPLICABLE);
    }
}