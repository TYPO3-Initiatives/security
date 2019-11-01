<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Security\Policy\ExpressionLanguage;

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
 * @todo Move into extension `backend`.
 */
class ResourceFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            $this->getHasPermissionFunction(),
        ];
    }

    protected function getHasPermissionFunction(): ExpressionFunction
    {
        return new ExpressionFunction(
            'hasPermission',
            function () {
                // Not implemented, we only use the evaluator
            },
            function ($variables, ...$arguments) {
                if (count($arguments) === 4) {
                    foreach ($variables['resource']->permissions as $permission) {
                        if (
                            $permission->principal->class === $arguments[0]
                            && $permission->resource === $arguments[1]
                            && $permission->action === $arguments[2]
                            && $permission->state === $arguments[3]
                        ) {
                            return true;
                        }
                    }
                }

                return false;
            }
        );
    }
}