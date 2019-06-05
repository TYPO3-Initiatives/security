<?php
defined('TYPO3_MODE') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['security'] = [
    'identityRetrival' => [
        \TYPO3\CMS\Backend\Permission\BackendIdentityRetrivalStrategy::class
    ],
    'permissionRetrival' => [
        \TYPO3\CMS\Backend\Permission\TablePermissionRetrivalStrategy::class,
    ],
];

if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['security_permission'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['security_permission'] = [];
}