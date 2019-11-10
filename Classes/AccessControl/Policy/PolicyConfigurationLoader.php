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

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Resolves the expression language provider configuration and stores it in a cache.
 */
class PolicyConfigurationLoader
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheIdentifier = 'policies-configuration';

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
        $this->packageManager = GeneralUtility::makeInstance(PackageManager::class, $dependencyOrderingService);
        $this->yamlFileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);
    }

    /**
     * @return array
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @todo Preparsed expressions in cache
     * @todo Validate before cache
     */
    public function getPolicyConfiguration(): array
    {
        if ($this->cache->has($this->cacheIdentifier)) {
            return $this->cache->require($this->cacheIdentifier);
        }

        $packages = $this->packageManager->getActivePackages();
        $entry = [];

        foreach ($packages as $package) {
            $packageConfiguration = $package->getPackagePath() . 'Configuration/Security/AccessControl/Policies.yaml';

            if (!file_exists($packageConfiguration)) {
                continue;
            }

            $policiesInPackage = $this->yamlFileLoader->load($packageConfiguration);
            if (isset($policiesInPackage['TYPO3']['CMS']['Policy'])) {
                $entry[] = $policiesInPackage['TYPO3']['CMS']['Policy'];
            }
        }

        $entry = count($entry) > 0 ? array_replace_recursive(...$entry) : $entry;

        $this->cache->set($this->cacheIdentifier, 'return ' . var_export($entry ?? [], true) . ';');

        return $entry ?? [];
    }
}
