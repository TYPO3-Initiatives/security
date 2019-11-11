<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Event;

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

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Security\AccessControl\Attribute\PrincipalAttribute;

/**
 * @api
 */
final class SubjectRetrivalEvent
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var array
     */
    private $principals;

    public function __construct(Context $context)
    {
        $this->context = $context;
        $this->principals = [];
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function addPrincipal(PrincipalAttribute $principal)
    {
        $this->principals[] = $principal;
    }

    public function getPrincipals(): array
    {
        return $this->principals;
    }
}