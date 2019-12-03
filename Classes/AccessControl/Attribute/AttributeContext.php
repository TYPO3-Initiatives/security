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

use TYPO3\AccessControl\Attribute\AttributeContextInterface;
use TYPO3\CMS\Core\Context\Context;

/**
 * @api
 */
class AttributeContext implements AttributeContextInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @inheritdoc
     */
    public function getEntry(string $key): ?object
    {
        if ($key === Context::class) {
            return $this->context;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function hasEntry(string $key): bool
    {
        return $key === Context::class;
    }

    /**
     * @inheritdoc
     */
    public function getKeys(): array
    {
        return [
            Context::class
        ];
    }
}