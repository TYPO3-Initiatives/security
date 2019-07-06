<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Policy\ExpressionLanguage\Attribute;

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

use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ResourceAttribute;

/**
 * @api
 * @todo Should be part of the extension `backend`
 */
final class ModuleResourceAttribute extends ResourceAttribute
{
    /**
     * @inheritdoc
     */
    const TYPE = 'backend.module';

    /**
     * @var tring
     */
    public $name;

    /**
     * Creates a module resource attribute.
     *
     * @param int $name Module name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);

        $this->name = $name;
    }
}