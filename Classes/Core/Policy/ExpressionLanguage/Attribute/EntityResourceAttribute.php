<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Policy\ExpressionLanguage\Attribute;

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

use TYPO3\CMS\Security\Policy\ExpressionLanguage\Attribute\ResourceAttribute;

/**
 * @api
 * @todo Should be part of the extension `core`
 */
final class EntityResourceAttribute extends ResourceAttribute
{
    /**
     * @inheritdoc
     */
    const TYPE = 'core.entity';

    /**
     * @var string
     */
    public $entity;

    /**
     * @var int
     */
    public $uid;

    /**
     * Creates an entity resource attribute.
     *
     * @param string $entity Entity definition name
     * @param int $uid Entity unique identifier
     */
    public function __construct(string $entity, int $uid = null)
    {
        parent::__construct($uid ? $entity . ':' . (string) $uid : $entity);

        $this->uid = $uid;
        $this->entity = $entity;
    }
}