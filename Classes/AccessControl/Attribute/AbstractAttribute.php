<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Attribute;

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

use TYPO3\CMS\Security\AccessControl\Utility\AttributeUtility;

/**
 * @api
 */
abstract class AbstractAttribute
{
    /**
     * @var array
     */
    private static $meta = [];

    /**
     * Returns attribute meta
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'classes':
                if (!isset(self::$meta[$name][static::class])) {
                    $classes = array_merge([static::class], class_parents(static::class));

                    foreach ($classes as $class) {
                        self::$meta[$name][static::class][] = AttributeUtility::translateClassNameToPolicyName($class);
                    }
                }

                return self::$meta[$name][static::class];
            case 'class':
                if (!isset(self::$meta[$name][static::class])) {
                    self::$meta[$name][static::class] = AttributeUtility::translateClassNameToPolicyName(static::class);
                }

                return self::$meta[$name][static::class];
            default:
                throw new \RuntimeException(sprintf('Unknown meta attribute "%s"', $name), 1572800990);
        }
    }
}
