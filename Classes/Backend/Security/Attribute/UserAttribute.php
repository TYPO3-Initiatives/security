<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Backend\Security\Attribute;

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

use TYPO3\CMS\Security\Attribute\PrincipalAttribute;

/**
 * @api
 * @todo Move into extension `backend`.
 */
class UserAttribute extends PrincipalAttribute
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $uid;

    /**
     * Creates a backend user principal attribute.
     *
     * @param int $uid Unique identifier
     * @param string $name User name
     */
    public function __construct(int $uid, string $name)
    {
        parent::__construct($name);

        $this->uid = $uid;
        $this->name = $name;
    }
}