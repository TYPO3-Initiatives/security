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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Service\DependencyOrderingService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ProviderConfigurationLoader
 * This class resolves the expression language provider configuration and store in a cache.
 */
class PolicyConfigurationLoader
{
    protected $cacheIdentifier = 'policies';

    /**
     * @return array
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     * @support Preparsed expressions in cache
     */
    public function getPolicyConfiguration(): array
    {
        $packageManager = GeneralUtility::makeInstance(
            PackageManager::class,
            GeneralUtility::makeInstance(DependencyOrderingService::class)
        );
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('security');
        $yamlFileLoader = GeneralUtility::makeInstance(YamlFileLoader::class);

        if ($cache->has($this->cacheIdentifier)) {
            return $cache->require($this->cacheIdentifier);
        }

        $packages = $packageManager->getActivePackages();
        $policies = [];
        foreach ($packages as $package) {
            $packageConfiguration = $package->getPackagePath() . 'Configuration/Yaml/Policies.yaml';
            if (file_exists($packageConfiguration)) {
                $policiesInPackage = $yamlFileLoader->load($packageConfiguration);
                if (isset($policiesInPackage['TYPO3']['CMS']['Policy'])) {
                    $policies[] = $policiesInPackage['TYPO3']['CMS']['Policy'];
                }
            }
        }
        $policies = count($policies) > 0 ? array_replace_recursive(...$policies) : $policies;
        $cache->set($this->cacheIdentifier, 'return ' . var_export($policies ?? [], true) . ';');
        return $policies ?? [];
    }
}
