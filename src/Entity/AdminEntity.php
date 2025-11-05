<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exceptions\EntityNotPersistedYet;
use Override;
use Stringable;

abstract class AdminEntity extends BaseEntity implements Stringable
{
    public function getIdAsString(): string
    {
        try {
            return (string) $this->getId();
        } catch (EntityNotPersistedYet) {
            return 'New ' . static::class;
        }
    }

    #[Override]
    public function __toString(): string
    {
        return $this->getIdAsString();
    }
}
