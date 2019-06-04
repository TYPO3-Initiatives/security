<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Security\Permission\Exception\NotSupportedException;
use Webmozart\Assert\Assert;

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

class PermissionProvider implements PermissionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function findList(ObjectIdentityInterface $objectIdentity, array $subjectIdentities = []): PermissionListInterface
    {
        $retrivalStrategies = $GLOBALS['TYPO3_CONF_VARS']['SYS']['security']['permissionRetrival'];

        foreach ($retrivalStrategies as $retrivalStrategy) {
            $retrivalStrategy = GeneralUtility::makeInstance($retrivalStrategy);

            Assert::isInstanceOf($retrivalStrategy, PermissionRetrivalStrategyInterface::class);

            if ($retrivalStrategy->canRetrive($objectIdentity)) {
                return $retrivalStrategy->retrive($objectIdentity, $subjectIdentities);
            }
        }

        throw new NotSupportedException();
    }
}