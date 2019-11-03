<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Attribute;

use TYPO3\CMS\Security\Utility\AttributeUtility;

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
class ResourceAttribute extends AbstractAttribute
{
    /**
     * @var string
     */
    public $identifier;

    /**
     * Creates a resource attribute.
     *
     * @param string $identifier Resource identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $this->class . AttributeUtility::NAMESPACE_SEPARATOR
            . AttributeUtility::translateClassNameToPolicyName($identifier);
    }
}