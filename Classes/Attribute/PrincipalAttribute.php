<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Attribute;

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

use TYPO3\CMS\Security\Utility\AttributeUtility;

/**
 * @api
 */
class PrincipalAttribute extends AbstractAttribute
{
    /**
     * @var string
     */
    public $identifier;

    /**
     * Creates a principal attribute.
     *
     * @param string $identifier Principal identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $this->class . AttributeUtility::NAMESPACE_SEPARATOR
        . AttributeUtility::translateClassNameToPolicyName($identifier);
    }
}