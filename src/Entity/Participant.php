<?php

declare(strict_types=1);

namespace App\Entity;

use App\Exceptions\MissingRequiredField;
use App\Repository\ParticipantRepository;
use Deprecated;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'participant')]
class Participant extends AdminEntity implements UserInterface
{
    #[Assert\Email]
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

    /**
     * @inheritDoc
     */
    #[Override]
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    #[Deprecated]
    #[Override]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        $identifier = $this->email;
        $identifier = trim($identifier);
        if ($identifier === '') {
            throw MissingRequiredField::create('email', self::class);
        }

        return $identifier;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->name;
    }
}
