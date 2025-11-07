<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\AnswerType;
use App\Validator\QuestionAnswers;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Component\Validator\Constraints as Assert;

#[QuestionAnswers]
#[ORM\Entity]
#[ORM\Table(name: 'question')]
class Question extends AdminEntity implements OrderIndexInterface
{
    use OrderIndexTrait;

    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'questions')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: false)]
    private Survey $survey;

    #[Assert\NotBlank]
    #[ORM\Column(name: '`text`', type: Types::TEXT, nullable: false)]
    private string $text = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 32, nullable: false, enumType: AnswerType::class)]
    private AnswerType $answerType;

    /**
     * @var Collection<array-key, QuestionOption>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: QuestionOption::class,
        mappedBy: 'question',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    #[ORM\OrderBy(['orderIndex' => 'ASC'])]
    private Collection $questionOptions;

    public function __construct()
    {
        $this->questionOptions = new ArrayCollection();
        $this->orderIndex = OrderIndexInterface::INIT_ORDER_INDEX;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function setSurvey(Survey $survey): void
    {
        $this->survey = $survey;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getAnswerType(): AnswerType
    {
        return $this->answerType;
    }

    public function setAnswerType(AnswerType $answerType): void
    {
        $this->answerType = $answerType;
    }

    /**
     * @return Collection<array-key, QuestionOption>
     */
    public function getQuestionOptions(): Collection
    {
        return $this->questionOptions;
    }

    public function addQuestionOption(QuestionOption $questionOption): void
    {
        if ($this->questionOptions->contains($questionOption)) {
            return;
        }
        $this->questionOptions->add($questionOption);
        $questionOption->setQuestion($this);

        $max = 0;
        foreach ($this->questionOptions as $item) {
            $max = max($max, $item->getOrderIndex());
        }
        $questionOption->updateOrderIndexIfNotSet($max + 1);
    }

    public function removeQuestionOption(QuestionOption $questionOption): void
    {
        $this->questionOptions->removeElement($questionOption);
    }

    #[Override]
    public function __toString(): string
    {
        return trim(sprintf('%s -> [%s] %s', $this->survey, $this->getIdAsString(), $this->text));
    }
}
