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
use TYPO3\CMS\Security\AccessControl\Attribute\AbstractAttribute;
use TYPO3\CMS\Security\AccessControl\Attribute\SubjectAttribute;

/**
 * @api
 */
final class AttributeRetrivalEvent
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var AbstractAttribute
     */
    private $attribute;

    /**
     * @var SubjectAttribute
     */
    private $subjectAttribute;

    public function __construct(AbstractAttribute $attribute, Context $context, SubjectAttribute $subjectAttribute)
    {
        $this->attribute = $attribute;
        $this->context = $context;
        $this->subjectAttribute = $subjectAttribute;
    }

    public function getAttribute(): AbstractAttribute
    {
        return $this->attribute;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public function getSubject(): SubjectAttribute
    {
        return $this->subjectAttribute;
    }
}
