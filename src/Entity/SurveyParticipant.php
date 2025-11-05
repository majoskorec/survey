<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\SurveyParticipantStatus;
use App\Model\SurveyStatus;
use App\Repository\SurveyParticipantRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * @psalm-type AnswerData = array<string, string|array<string>>
 */
#[ORM\Entity(repositoryClass: SurveyParticipantRepository::class)]
#[ORM\Table(name: 'survey_participant')]
#[ORM\UniqueConstraint(name: 'uniq_survey_participant', columns: ['survey_id', 'participant_id'])]
class SurveyParticipant extends BaseEntity
{
    #[ORM\ManyToOne(targetEntity: Survey::class, inversedBy: 'surveyParticipants')]
    #[ORM\JoinColumn(name: 'survey_id', referencedColumnName: 'id', nullable: false)]
    private Survey $survey;

    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', nullable: false)]
    private Participant $participant;

    #[ORM\Column(type: Types::STRING, length: 36, unique: true, nullable: false)]
    private string $linkToken;

    #[ORM\Column(type: Types::STRING, length: 32, nullable: false, enumType: SurveyParticipantStatus::class)]
    private SurveyParticipantStatus $status;

    /**
     * @var AnswerData|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $answers = null;

    public static function createNew(
        Survey $survey,
        Participant $participant,
    ): self {
        $entity = new self();
        $entity->linkToken = Uuid::v7()->toString();
        $entity->survey = $survey;
        $entity->participant = $participant;
        $entity->status = SurveyParticipantStatus::CREATED;

        return $entity;
    }

    public function getSurvey(): Survey
    {
        return $this->survey;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    public function getLinkToken(): string
    {
        return $this->linkToken;
    }

    public function canEdit(): bool
    {
        return $this->status !== SurveyParticipantStatus::CREATED
            && $this->survey->getStatus() === SurveyStatus::PUBLISHED;
    }

    /**
     * @return AnswerData|null
     */
    public function getAnswers(): ?array
    {
        return $this->answers;
    }

    public function send(): void
    {
        $this->status = SurveyParticipantStatus::SENT;
    }

    /**
     * @param AnswerData $answers
     */
    public function complete(array $answers): void
    {
        $this->answers = $answers;
        $this->status = SurveyParticipantStatus::COMPLETED;
    }
}
