<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;

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

interface SubjectIdentityRetrivalStrategyInterface
{
    /**
     * Retrieves the available subject identities for the given authentication.
     *
     * @return SecurityIdentityInterface[]
     * @todo Replace AbstractUserAuthentication with an interface (e.g. AccessTokenInterface)
     */
    public function getSubjectIdentities(AbstractUserAuthentication $authentication): array;
}