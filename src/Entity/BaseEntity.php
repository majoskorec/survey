<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exceptions\EntityNotPersistedYet;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class BaseEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    protected ?int $id = null;

    public function getId(): int
    {
        return $this->id ?? throw EntityNotPersistedYet::withEntity($this);
    }
}
