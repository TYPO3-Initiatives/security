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

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\AccessControl\Policy\Expression\ResolverInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\ExpressionLanguage\ProviderConfigurationLoader;

/**
 * @internal
 */
class ExpressionResolver implements ResolverInterface
{
    /**
     * @var ProviderInterface
     */
    protected $provider;

    /**
     * @var ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var array
     */
    public $attributes = [];

    /**
     * @param string $context
     * @param array $variables
     */
    public function __construct()
    {
        $functionProviderInstances = [];
        $providers = GeneralUtility::makeInstance(ProviderConfigurationLoader::class)->getExpressionLanguageProviders()['policy'] ?? [];
        $providers = array_unique($providers);
        $functionProviders = [];
        $this->attributes = [];
        foreach ($providers as $provider) {
            $providerInstance = GeneralUtility::makeInstance($provider);
            $functionProviders[] = $providerInstance->getExpressionLanguageProviders();
            $this->attributes[] = $providerInstance->getExpressionLanguageVariables();
        }
        $functionProviders = array_merge(...$functionProviders);
        $this->attributes = array_replace_recursive(...$this->attributes);
        foreach ($functionProviders as $functionProvider) {
            $functionProviderInstances[] = GeneralUtility::makeInstance($functionProvider);
        }
        $this->expressionLanguage = new ExpressionLanguage(null, $functionProviderInstances);
    }

    /**
     * @inheritdoc
     */
    public function evaluate(string $expression, array $attributes): bool
    {
        return (bool)$this->expressionLanguage->evaluate($expression, array_replace_recursive($this->attributes, $attributes));
    }

    /**
     * @inheritdoc
     */
    public function validate(string $expression): void
    {
        $this->expressionLanguage->parse($expression, array_keys($this->attributes));
    }
}
