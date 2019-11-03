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

use TYPO3\CMS\Security\Attribute\AbstractAttribute;
use TYPO3\CMS\Security\Attribute\PrincipalAttribute;

/**
 * @api
 * @todo Move into extension `backend`.
 */
final class PermissionAttribute extends AbstractAttribute
{
    /**
     * @var string
     */
    const STATE_PERMIT = 'permit';

    /**
     * @var string
     */
    const STATE_DENY = 'deny';

    /**
     * @var string
     */
    public $action;

    /**
     * @var PrincipalAttribute
     */
    public $principal;

    /**
     * @var string
     */
    public $resource;

    /**
     * @var string
     */
    public $state;

    /**
     * Creates a backend permission attribute.
     *
     * @param PrincipalAttribute $principal Principal
     * @param string $state Resource identifier
     * @param string $action Action identifier
     * @param string $state State identifier
     */
    public function __construct(PrincipalAttribute $principal, string $resource, string $action, string $state)
    {
        $this->action = $action;
        $this->principal = $principal;
        $this->resource = $resource;
        $this->state = $state;
    }
}