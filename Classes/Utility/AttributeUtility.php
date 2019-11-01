<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Utility;

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
class AttributeUtility
{
    public static function translateClassNameToPolicyName($name) {
        return strtolower(preg_replace(
            [
                '/(^[^\\\\]+\\\\|\\\\Security\\\\Attribute|Attribute$)/', 
                '/\\\\/',
                '/(?<=[A-Z])(?=[A-Z][a-z])|(?<=[^A-Z:])(?=[A-Z])|(?<=[A-Za-z])(?=[^A-Za-z0-9:])/'
            ],
            [
                '', 
                ':', 
                '-'
            ],
            $name
        ));
    }
}