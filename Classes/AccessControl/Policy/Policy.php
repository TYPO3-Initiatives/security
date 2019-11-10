<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Policy;

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
use TYPO3\CMS\Security\AccessControl\Policy\Evaluation\EvaluatorInterface;
use TYPO3\CMS\Security\AccessControl\Policy\PolicyDecision;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class Policy extends AbstractPolicy
{
    /**
     * @var EvaluatorInterface
     */
    private $evaluator;

    /**
     * @var PolicyRule[]
     */
    private $rules;

    public function __construct(
        string $id,
        array $rules,
        EvaluatorInterface $evaluator,
        ?string $description = null,
        ?string $target = null,
        ?int $priority = null,
        ?array $denyObligations = null,
        ?array $permitObligations = null
    ) {
        Assert::notEmpty($rules);
        Assert::allIsInstanceOf($rules, PolicyRule::class);

        parent::__construct($id, $description, $target, $priority, $denyObligations, $permitObligations);

        $this->evaluator = $evaluator;
        $this->rules = array_combine(array_map(function ($rule) {
            return $rule->getId();
        }, $rules), array_values($rules));
    }

    public function evaluate(Resolver $resolver): PolicyDecision
    {
        if ($this->target !== null && !$resolver->evaluate($this->target)) {
            return new PolicyDecision(PolicyDecision::NOT_APPLICABLE);
        }

        $decision = $this->evaluator->process($resolver, ...array_values($this->rules));

        if (!$decision->isApplicable()) {
            return $decision;
        }

        if ($decision->getValue() === PolicyDecision::PERMIT) {
            return $decision->add(...$this->permitObligations);
        }

        return $decision->add(...$this->denyObligations);
    }

    public function getIterator()
    {
        return $this->rules;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->rules[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->rules[$offset] ?? null;
    }

    /**
     * @return PolicyRule[]
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}