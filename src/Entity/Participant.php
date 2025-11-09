<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'participant')]
class Participant extends AdminEntity
{
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    #[Assert\NotBlank]
    #[ORM\Column(length: 180, unique: true)]
    protected string $email;

    #[Assert\NotBlank]
    #[ORM\Column(length: 180)]
    protected string $name;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->name;
    }
}
