<?php
declare(strict_types = 1);

return [
    'policy' => [
        \TYPO3\CMS\Security\AccessControl\Policy\ExpressionLanguage\EnvironmentProvider::class,
        \TYPO3\CMS\Security\AccessControl\Policy\ExpressionLanguage\SubjectProvider::class,
    ],
];
