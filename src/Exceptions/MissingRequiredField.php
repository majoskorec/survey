<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\PropertyAccess\Exception\UninitializedPropertyException;

final class MissingRequiredField extends UninitializedPropertyException
{
    public static function create(string $fieldName, string $entityName): self
    {
        return new self(sprintf('The required field `%s` is missing in entity `%s`.', $fieldName, $entityName));
    }
}
