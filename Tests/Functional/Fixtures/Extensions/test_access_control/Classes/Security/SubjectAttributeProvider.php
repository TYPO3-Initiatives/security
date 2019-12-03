<?php
declare(strict_types = 1);

namespace Example\AccessControl\Security;

use TYPO3\AccessControl\Attribute\PrincipalAttribute;
use TYPO3\AccessControl\Event\SubjectRetrievalEvent;

class SubjectAttributeProvider
{
    public function __invoke(SubjectRetrievalEvent $event): void
    {
        $event->addPrincipal(new PrincipalAttribute('foo'));
    }
}
