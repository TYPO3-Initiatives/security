<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Security\AccessControl\Policy;

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

use ArrayAccess;
use IteratorAggregate;
use TYPO3\CMS\Security\AccessControl\Policy\Evaluation\EvaluableInterface;
use TYPO3\CMS\Security\AccessControl\Policy\Exception\NotSupportedMethodException;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
abstract class AbstractPolicy implements EvaluableInterface, IteratorAggregate, ArrayAccess
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var PolicyObligations[]
     */
    protected $denyObligations;

    /**
     * @var PolicyObligations[]
     */
    protected $permitObligations;

    public function __construct(
        string $id,
        ?string $description,
        ?string $target,
        ?int $priority,
        ?array $denyObligations,
        ?array $permitObligations
    ) {
        Assert::stringNotEmpty($id, '$id must not be empty');
        Assert::allIsInstanceOf($denyObligations ?? [], PolicyObligation::class);
        Assert::allIsInstanceOf($permitObligations ?? [], PolicyObligation::class);

        $this->id = $id;
        $this->description = $description;
        $this->target = $target;
        $this->priority = $priority ?? 1;
        $this->denyObligations = $denyObligations ?? [];
        $this->permitObligations = $permitObligations ?? [];
    }

    public function offsetSet($offset, $value): void
    {
        throw new NotSupportedMethodException();
    }
    public function offsetUnset($offset): void
    {
        throw new NotSupportedMethodException();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return PolicyObligation[]
     */
    public function getDenyObligations(): array
    {
        return $this->denyObligations;
    }

    /**
     * @return PolicyObligation[]
     */
    public function getPermitObligations(): array
    {
        return $this->permitObligations;
    }
}