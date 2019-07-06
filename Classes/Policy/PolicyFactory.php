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

use InvalidArgumentException;
use TYPO3\CMS\Security\Policy\Evaluation\DenyOverridesEvaluator;
use TYPO3\CMS\Security\Policy\Evaluation\EvaluatorInterface;
use TYPO3\CMS\Security\Policy\Evaluation\FirstApplicableEvaluator;
use TYPO3\CMS\Security\Policy\Evaluation\HighestPriorityEvaluator;
use TYPO3\CMS\Security\Policy\Evaluation\PermitOverridesEvaluator;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
class PolicyFactory
{
    /**
     * @internal
     */
    public function build(array $configuration, ?string $name = null): AbstractPolicy
    {
        $configuration['id'] = $name ?? 'Root';

        $visited = [];
        $stack = [$configuration];
        $policies = [];

        // depth first search to build the graph
        while (end($stack)) {
            $configuration = end($stack);

            $visited[$configuration['id']] = true;

            foreach ($configuration['policies'] ?? [] as $id => $policy) {
                if (!isset($visited[$id])) {
                    $policy['id'] = $id;
                    $stack[] = $policy;
                    continue 2;
                }
            }

            if (isset($configuration['rules'])) {
                $policy = new Policy(
                    (string) $configuration['id'],
                    $this->buildRules($configuration['rules']),
                    $this->buildEvaluator($configuration['algorithm'] ?? null),
                    $configuration['description'] ?? null,
                    $configuration['target'] ?? null,
                    $configuration['priority'] ?? null,
                    $this->buildObligation($configuration['obligation']['deny'] ?? null),
                    $this->buildObligation($configuration['obligation']['permit'] ?? null)
                );
            } else if (isset($configuration['policies'])) {
                $policy = new PolicySet(
                    (string) $configuration['id'],
                    $policies[$configuration['id']] ?? [],
                    $this->buildEvaluator($configuration['algorithm'] ?? null),
                    $configuration['description'] ?? null,
                    $configuration['target'] ?? null,
                    $configuration['priority'] ?? null,
                    $this->buildObligation($configuration['obligation']['deny'] ?? null),
                    $this->buildObligation($configuration['obligation']['permit'] ?? null)
                );
            }

            if (!$policy instanceof AbstractPolicy || isset($configuration['rules']) && isset($configuration['policies'])) {
                throw new InvalidArgumentException(
                    sprintf('Unexpected policy block "%s"', $configuration['id']),
                    1561758166
                );
            }

            $previous = prev($stack);

            if (!$previous) {
                return $policy;
            }

            $policies[$previous['id']][$configuration['id']] = $policy;

            array_pop($stack);
        }
    }

    /**
     * @return PolicyRule[]
     */
    protected function buildObligation(?array $configuration): array
    {
        $obligations = [];

        foreach ($configuration ?? [] as $operation => $arguments) {
            Assert::isArray($arguments);

            $obligations[] = new PolicyObligation(
                $operation,
                $arguments
            );
        }

        return $obligations;
    }

    /**
     * @return PolicyRule[]
     */
    protected function buildRules(array $configuration): array
    {
        $rules = [];

        foreach ($configuration as $id => $entry) {
            $rules[$id] = new PolicyRule(
                (string) $id,
                $entry['target'] ?? null,
                $entry['condition'] ?? null,
                $entry['effect'] ?? null,
                $entry['priority'] ?? null,
                $this->buildObligation($entry['obligation']['deny'] ?? null),
                $this->buildObligation($entry['obligation']['permit'] ?? null)
            );
        }

        return $rules;
    }

    protected function buildEvaluator(?string $algorithm): EvaluatorInterface
    {
        switch ($algorithm) {
            case 'denyOverrides':
                return new DenyOverridesEvaluator();
            case 'permitOverrides':
                return new PermitOverridesEvaluator();
            case 'highestPriority':
                return new HighestPriorityEvaluator();
            case 'firstApplicable':
            case null:
                return new FirstApplicableEvaluator();
        }

        throw new InvalidArgumentException(sprintf('Invalid combining algorithm "%s"', $algorithm), 1562719069);
    }
}