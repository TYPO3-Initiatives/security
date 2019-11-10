<?php
declare(strict_types = 1);

namespace Example\AccessControl\Security;

use TYPO3\CMS\Security\Attribute\PrincipalAttribute;
use TYPO3\CMS\Security\Event\SubjectRetrivalEvent;

class SubjectAttributeProvider
{
    public function __invoke(SubjectRetrivalEvent $event): void
    {
        $event->getSubject()->principals[] = new PrincipalAttribute('foo');
    }
}
