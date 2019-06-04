<?php
declare(strict_types = 1);
namespace TYPO3\CMS\Security\Permission;

use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
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

class SubjectIdentityProvider implements SubjectIdentityProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSubjectIdentities(AbstractUserAuthentication $authentication): array
    {
        $retrivalStrategies = $GLOBALS['TYPO3_CONF_VARS']['SYS']['security']['identityRetrival'];

        foreach ($retrivalStrategies as $retrivalStrategy) {
            $retrivalStrategy = GeneralUtility::makeInstance($retrivalStrategy);

            Assert::isInstanceOf($retrivalStrategy, SubjectIdentityRetrivalStrategyInterface::class);

            if ($retrivalStrategy->canRetrive($authentication)) {
                return $retrivalStrategy->retrive($authentication);
            }
        }

        throw new NotSupportedException();
    }
}