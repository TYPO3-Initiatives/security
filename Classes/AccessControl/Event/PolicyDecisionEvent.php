<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Event;

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
use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;

/**
 * @api
 */
final class PolicyDecisionEvent
{
    /**
     * @var PolicyDecision
     */
    private $decision;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $attributes;

    public function __construct(PolicyDecision $decision, Context $context, array $attributes)
    {
        $this->decision = $decision;
        $this->context = $context;
        $this->attributes = $attributes;
    }

    public function getDecision(): PolicyDecision
    {
        return $this->decision;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
