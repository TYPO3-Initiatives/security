<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Policy\Evaluation;

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
use TYPO3\CMS\Security\Policy\PolicyDecision;

/**
 * @internal
 */
class PermitOverridesEvaluator implements EvaluatorInterface
{
    public function process(Resolver $resolver, EvaluableInterface ...$evaluables): PolicyDecision
    {
        $decision = new PolicyDecision(PolicyDecision::NOT_APPLICABLE);

        foreach ($evaluables as $evaluable) {
            $next = $evaluable->evaluate($resolver);

            if ($next->getValue() === PolicyDecision::PERMIT) {
                return $next;
            }

            if ($next->isApplicable()) {
                $decision = $decision->isApplicable() ? $decision->merge($next) : $next;
            }
        }

        return $decision;
    }
}