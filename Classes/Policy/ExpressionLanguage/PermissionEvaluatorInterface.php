<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Policy\ExpressionLanguage;

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

use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ActionAttribute;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ResourceAttribute;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\SubjectAttribute;

/**
 * Strategy used in policy expression evaluation to determine whether a subject has a permission for a given resource.
 * @api
 */
interface PermissionEvaluatorInterface
{
    /**
     * Returns whether the strategy is able to determine a subject has a permission for a given resource or not.
     *
     * @param ResourceAttribute $resource
     * @param ActionAttribute $actions
     * @return bool
     */
    public function canEvaluate(ResourceAttribute $resource, ActionAttribute $action): bool;

    /**
     * Returns true if permission is granted, false otherwise.
     *
     * @param SubjectAttribute $subject
     * @param ResourceAttribute $resource
     * @param ActionAttribute $action
     * @return bool
     */
    public function evaluate(SubjectAttribute $subject, ResourceAttribute $resource, ActionAttribute $action): bool;
}