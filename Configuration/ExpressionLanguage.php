<?php
declare(strict_types = 1);

return [
    'policy' => [
        \TYPO3\CMS\Security\Policy\ExpressionLanguage\SubjectProvider::class,
        \TYPO3\CMS\Backend\Security\Policy\ExpressionLanguage\ResourceProvider::class,
    ],
];
