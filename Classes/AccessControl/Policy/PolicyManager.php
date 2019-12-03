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

use TYPO3\AccessControl\Policy\PolicyFactory;
use TYPO3\AccessControl\Policy\AbstractPolicy;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Resolves the expression language provider configuration and stores it in a cache.
 */
final class PolicyManager
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var string
     */
    const CACHE_IDENTIFIER = 'policies';

    /**
     * @var PackageManager
     */
    private $packageManager;

    /**
     * @var YamlFileLoader
     */
    private $yamlFileLoader;

    public function __construct(FrontendInterface $cache)
    {
        $dependencyOrderingService = GeneralUtility::makeInstance(DependencyOrderingService::class);

        $this->cache = $cache;
        $this->packageManager = GeneralUtility::makeInstance(PackageManager::class);//, $dependencyOrderingService);
        $this->yamlFileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
        $this->policyFactory = GeneralUtility::makeInstance(PolicyFactory::class);
        $this->defaultResolver = GeneralUtility::makeInstance(ExpressionResolver::class, ['subject', 'resource', 'action']);
    }

    /**
     * @return array
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @todo Preparsed expressions in cache
     * @todo Validate before cache
     */
    public function getPolicy(): ?AbstractPolicy
    {
        if ($this->cache->has(self::CACHE_IDENTIFIER)) {
            $configuration = $this->cache->require(self::CACHE_IDENTIFIER);
        } else {
            $packages = $this->packageManager->getActivePackages();
            $configuration = [];

            foreach ($packages as $package) {
                $packageConfiguration = $package->getPackagePath() . 'Configuration/Security/AccessControl/Policies.yaml';

                if (!file_exists($packageConfiguration)) {
                    continue;
                }

                $packageConfiguration = $this->yamlFileLoader->load($packageConfiguration);
                if (isset($packageConfiguration['TYPO3']['CMS']['Policy'])) {
                    $configuration[] = $packageConfiguration['TYPO3']['CMS']['Policy'];
                }
            }

            $configuration = count($configuration) > 0 ? array_replace_recursive(...$configuration) : $configuration;

            $this->cache->set(self::CACHE_IDENTIFIER, 'return ' . var_export($configuration ?? [], true) . ';');
        }

        $policy = $this->policyFactory->build($configuration, $this->defaultResolver);

        return $policy;
    }
}
