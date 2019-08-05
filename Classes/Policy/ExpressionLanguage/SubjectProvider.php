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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\SubjectAttribute;

/**
 * @internal
 */
class SubjectProvider extends AbstractProvider
{
    public function __construct(Context $context = null)
    {
        $context = $context ?? GeneralUtility::makeInstance(Context::class);
        $principals = [];
        $principalProviders = $GLOBALS['TYPO3_CONF_VARS']['SYS']['security']['principalProvider'];

        foreach ($principalProviders as $principalProvider) {
            $principals = array_merge(
                $principals,
                GeneralUtility::makeInstance($principalProvider)->provide($context)
            );
        }

        $subject = new SubjectAttribute(uniqid(), ...$principals);

        $this->expressionLanguageVariables = [
            'subject' => $subject,
        ];

        $this->expressionLanguageProviders = [
            SubjectFunctionsProvider::class,
         ];
    }
}
