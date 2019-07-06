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

/**
 * @api
 * @todo Should be part of the extension `core`
 */
final class PageResourceAttribute extends EntityResourceAttribute
{
    /**
     * @inheritdoc
     */
    const TYPE = 'core.page';

    /**
     * Creates an page resource attribute.
     *
     * @param int $uid Page unique identifier
     */
    public function __construct(int $uid)
    {
        parent::__construct($uid, 'pages');
    }
}