<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Policy\ExpressionLanguage;

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

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * @internal
 */
class SubjectFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            $this->getHasAuthorityFunction(),
        ];
    }

    protected function getHasAuthorityFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'hasAuthority',
            function () {
                // Not implemented, we only use the evaluator
            },
            function ($variables, ...$arguments) {
                if (count($arguments) == 1) {
                    return count(
                        array_filter($variables['subject']->principals, function ($principal) use ($arguments) {
                            return $principal->identifier === $arguments[0];
                        })
                    ) > 0;
                }

                return false;
            }
        );
    }
}