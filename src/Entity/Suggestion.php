<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\DatePointType;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\Validator\Constraints as Assert;

use function Symfony\Component\Clock\now;

#[ORM\Entity]
#[ORM\Table(name: 'suggestion')]
class Suggestion extends AdminEntity
{
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $suggestion;

    #[ORM\Column(type: DatePointType::NAME, nullable: false)]
    private readonly DatePoint $createdAt;

    public function __construct()
    {
        $this->createdAt = now();
    }

    public function getCreatedAt(): DatePoint
    {
        return $this->createdAt;
    }

    public function getSuggestion(): string
    {
        return $this->suggestion;
    }

    public function setSuggestion(string $suggestion): void
    {
        $this->suggestion = $suggestion;
    }
}
