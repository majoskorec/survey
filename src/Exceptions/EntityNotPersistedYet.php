<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException;

final class EntityNotPersistedYet extends UninitializedPropertyException
{
    public static function withEntity(object $entity): self
    {
        return new self(sprintf('The entity of class `%s` has not been persisted yet.', $entity::class));
    }
}
