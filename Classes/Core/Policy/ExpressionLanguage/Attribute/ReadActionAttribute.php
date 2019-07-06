<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute;

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

use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ActionAttribute;

/**
 * @api
 * @todo Should be part of the extension `core`
 */
final class ReadActionAttribute extends ActionAttribute
{
    /**
     * @inheritdoc
     */
    const TYPE = 'core.read';

    /**
     * Creates a read action attribute
     */
    public function __construct()
    {
        parent::__construct(self::TYPE);
    }
}