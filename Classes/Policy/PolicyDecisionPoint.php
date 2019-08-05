<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Policy;

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
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Policy\Event\AfterPolicyDecisionEvent;
use Webmozart\Assert\Assert;

/**
 * Policy decision point
 * @api
 */
class PolicyDecisionPoint implements SingletonInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var AbstractPolicy
     */
    protected $rootPolicy;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @todo Support custom context, therefore the expression resolver muste pass a custom context to the expression provider
     */
    public function __construct()
    {
        $policyConfiguration = GeneralUtility::makeInstance(PolicyConfigurationLoader::class)->getPolicyConfiguration();

        $this->rootPolicy = GeneralUtility::makeInstance(PolicyFactory::class)->build($policyConfiguration);
        $this->context = GeneralUtility::makeInstance(Context::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
    }

    /**
     * Authorize an access request
     *
     * @param array $attributes Attributes of the access request
     * @return PolicyDecision Authorization decision for the request
     */
    public function authorize(array $attributes): PolicyDecision
    {
        $policyExpressionResolver = GeneralUtility::makeInstance(Resolver::class, 'policy', $attributes);

        $decision = $this->rootPolicy->evaluate($policyExpressionResolver);

        $this->eventDispatcher->dispatch(
            new AfterPolicyDecisionEvent(
                $decision,
                $this->context,
                $attributes
            )
        );

        return $decision;
    }
}