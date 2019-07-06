<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute;

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

/**
 * @api
 */
abstract class AbstractAttribute
{
    /**
     * @var string
     */
    const TYPE = 'security.attribute';

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $identifier;

    public function __construct(string $identifier)
    {
        $this->type = static::TYPE;
        $this->identifier = $identifier;
    }
}