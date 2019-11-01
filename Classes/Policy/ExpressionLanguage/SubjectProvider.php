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
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Attribute\SubjectAttribute;
use TYPO3\CMS\Security\Event\SubjectRetrivalEvent;
use TYPO3\CMS\Security\Policy\ExpressionLanguage\SubjectFunctionsProvider;

/**
 * @internal
 * @todo Retrive the principals only once and cache the result
 */
class SubjectProvider extends AbstractProvider
{
    public function __construct(Context $context = null)
    {
        $context = $context ?? GeneralUtility::makeInstance(Context::class);
        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);

        $subjectAttribute = new SubjectAttribute(uniqid());
        $event = new SubjectRetrivalEvent($context, $subjectAttribute);

        $eventDispatcher->dispatch($event);

        $this->expressionLanguageVariables = [
            'subject' => $event->getSubject(),
        ];

        $this->expressionLanguageProviders = [
            SubjectFunctionsProvider::class,
        ];
    }
}