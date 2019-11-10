<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\Policy;

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

use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Security\Attribute\SubjectAttribute;
use TYPO3\CMS\Security\Event\AttributeRetrivalEvent;
use TYPO3\CMS\Security\Event\SubjectRetrivalEvent;

/**
 * Policy information point
 * @api
 */
class PolicyInformationPoint
{
    /**
     * @var FrontendInterface
     */
    protected $cache;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, FrontendInterface $cache)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->cache = $cache;
    }

    public function obtain(array $attributes, Context $context): array
    {
        $attributes = array_filter($attributes, static function($key) {
            return $key !== 'subject';
        }, ARRAY_FILTER_USE_KEY);

        $cacheIdentifier = sha1(static::class . '_subject_' . serialize($context));

        if (($subjectAttribute = $this->cache->get($cacheIdentifier)) === false) {
            $subjectAttribute = new SubjectAttribute(uniqid());
            $subjectEvent = new SubjectRetrivalEvent($context, $subjectAttribute);

            $this->eventDispatcher->dispatch($subjectEvent);

            $subjectAttribute = $subjectEvent->getSubject();

            $this->cache->set($cacheIdentifier, $subjectAttribute);
        }

        foreach ($attributes as $attribute) {
            $this->eventDispatcher->dispatch(
                new AttributeRetrivalEvent($attribute, $context, $subjectAttribute)
            );
        }

        $attributes['subject'] = $subjectAttribute;

        return $attributes;
    }
}