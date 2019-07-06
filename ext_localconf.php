<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['security'] = [
    'frontend' => \TYPO3\CMS\Core\Cache\Frontend\PhpFrontend::class,
    'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
    'options' => [
        'defaultLifetime' => 0,
    ],
    'groups' => ['system']
];

$GLOBALS['TYPO3_CONF_VARS']['SYS']['security'] = [
    'permissionEvaluator' => [
        \TYPO3\CMS\Backend\Policy\ExpressionLanguage\PermissionEvaluator::class,
    ],
    'principalProvider' => [
        \TYPO3\CMS\Backend\Policy\ExpressionLanguage\PrincipalProvider::class,
    ],
];