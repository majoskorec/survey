<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'question_option')]
class QuestionOption extends AdminEntity implements OrderIndexInterface
{
    use OrderIndexTrait;

    #[ORM\ManyToOne(targetEntity: Question::class, inversedBy: 'questionOptions')]
    #[ORM\JoinColumn(name: 'question_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Question $question;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $label = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: false)]
    private string $value = '';

    public function __construct()
    {
        $this->orderIndex = OrderIndexInterface::INIT_ORDER_INDEX;
    }

    public function getQuestion(): Question
    {
        return $this->question;
    }

    public function setQuestion(Question $question): void
    {
        $this->question = $question;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf('%s -> [%s] %s', $this->question, $this->getIdAsString(), $this->value);
    }
}
