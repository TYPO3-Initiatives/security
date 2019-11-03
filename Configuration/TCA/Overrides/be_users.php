<?php
defined('TYPO3_MODE') or die();

call_user_func(function () {

    // @TODO: Move into extension `backend`.
    $additionalColumns = [
        'permissions' => [
            'exclude' => true,
            'label' => 'Advanced Permissions',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 30,
                'max' => 2000,
            ],
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users', $additionalColumns);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'permissions', '0', 'after:allowed_languages');
});