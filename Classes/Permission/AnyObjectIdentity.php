<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

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
  * Any object identity implementation.
  */
class AnyObjectIdentity extends ObjectIdentity
{
    public function __construct()
    {
        parent::__construct('anything');
    }

    /**
     * {@inheritdoc}
     */
    public function equals(ObjectIdentityInterface $identity): bool
    {
        return true;
    }
}