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

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\ExpressionLanguage\Resolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Event\PolicyDecisionEvent;

/**
 * Policy decision point
 * @api
 */
class PolicyDecisionPoint
{
    /**
     * @var FrontendInterface
     */
    protected $cache;

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
     * @var PolicyInformationPoint
     */
    protected $policyInformationPoint;

    public function __construct(
        Context $context,
        EventDispatcherInterface $eventDispatcher,
        FrontendInterface $cache,
        PolicyConfigurationLoader $policyConfigurationLoader,
        PolicyFactory $policyFactory,
        PolicyInformationPoint $policyInformationPoint
    ) {
        $this->rootPolicy = $policyFactory->build($policyConfigurationLoader->getPolicyConfiguration());
        $this->context = $context;
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;
        $this->policyInformationPoint = $policyInformationPoint;
    }

    /**
     * Authorize an access request
     *
     * @param array $attributes Attributes of the access request
     * @return PolicyDecision Authorization decision for the request
     */
    public function authorize(array $attributes): PolicyDecision
    {
        $attributes = $this->policyInformationPoint->obtain($attributes, $this->context);

        $policyExpressionResolver = GeneralUtility::makeInstance(Resolver::class, 'policy', $attributes);

        $decision = $this->rootPolicy->evaluate($policyExpressionResolver);

        $this->eventDispatcher->dispatch(new PolicyDecisionEvent($decision, $this->context, $attributes));

        return $decision;
    }
}