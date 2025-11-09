<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\SurveyStatus;
use App\Repository\SurveyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Override;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SurveyRepository::class)]
#[ORM\Table(name: 'survey')]
#[UniqueEntity(fields: ['slug'], message: 'This slug is already in use.')]
class Survey extends AdminEntity
{
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private string $slug = '';

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $infoText = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, length: 16, enumType: SurveyStatus::class)]
    private SurveyStatus $status;

    /**
     * @var Collection<int, Question>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: Question::class,
        mappedBy: 'survey',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'id',
    )]
    #[ORM\OrderBy(['orderIndex' => 'ASC'])]
    private Collection $questions;

    /**
     * @var Collection<array-key, SurveyParticipant>
     */
    #[Assert\Valid]
    #[ORM\OneToMany(
        targetEntity: SurveyParticipant::class,
        mappedBy: 'survey',
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
    )]
    private Collection $surveyParticipants;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->surveyParticipants = new ArrayCollection();
        $this->status = SurveyStatus::DRAFT;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getStatus(): SurveyStatus
    {
        return $this->status;
    }

    public function setStatus(SurveyStatus $status): void
    {
        $this->status = $status;
    }

    public function publish(): void
    {
        $this->status = SurveyStatus::PUBLISHED;
    }

    public function close(): void
    {
        $this->status = SurveyStatus::CLOSED;
    }

    public function hide(): void
    {
        $this->status = SurveyStatus::HIDDEN;
    }

    /**
     * @return Collection<int, Question>
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): void
    {
        if ($this->questions->contains($question)) {
            return;
        }

        $this->questions->add($question);
        $question->setSurvey($this);

        $max = 0;
        foreach ($this->questions as $item) {
            $max = max($max, $item->getOrderIndex());
        }
        $question->updateOrderIndexIfNotSet($max + 1);
    }

    public function removeQuestion(Question $question): void
    {
        $this->questions->removeElement($question);
    }

    /**
     * @return Collection<array-key, SurveyParticipant>
     */
    public function getSurveyParticipants(): Collection
    {
        return $this->surveyParticipants;
    }

    public function getInfoText(): string
    {
        return $this->infoText;
    }

    public function setInfoText(string $infoText): void
    {
        $this->infoText = $infoText;
    }

    #[Override]
    public function __toString(): string
    {
        return trim(sprintf('[%s] %s', $this->getIdAsString(), $this->title));
    }
}
