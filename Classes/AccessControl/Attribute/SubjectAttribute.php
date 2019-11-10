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

/**
 * @api
 */
final class SubjectAttribute extends AbstractAttribute
{
    /**
     * @var string
     */
    public $identifier;

    /**
     * @var array
     */
    public $principals;

    /**
     * Creates a subject attribute.
     *
     * @param string $identifier Subject identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->principals = [];
    }
}