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

abstract class AbstractSubjectIdentity implements SubjectIdentityInterface
{
    /**
     * @var string
     */
    private $identifier;

    /**
     * @param string $identifier
     * @throws \InvalidArgumentException
     */
    public function __construct(string $identifier)
    {
        if (empty($identifier)) {
            throw new \InvalidArgumentException('$identifier cannot be empty.');
        }
        $this->identifier = $identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(SubjectIdentityInterface $identity): bool
    {
        if (!$identity instanceof self) {
            return false;
        }

        return $this->identifier === $identity->getIdentifier();
    }

     /**
      * Returns the identifier.
      *
      * @return string
      */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function __toString()
    {
        return sprintf('t3:/security/permission/subject/%s', $this->identifier);
    }
}