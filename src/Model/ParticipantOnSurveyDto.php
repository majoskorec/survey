<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Participant;

/**
 * doctrine cannot hydrate nullable entity into DTO
 */
final class ParticipantOnSurveyDto extends Participant
{
    public function __construct(
        public readonly Participant $participant,
        public readonly ?int $surveyParticipantId,
        public readonly ?string $linkToken,
        public readonly ?SurveyParticipantStatus $status,
    ) {
        $this->email = $participant->getEmail();
        $this->name = $participant->getName();
        $this->id = $participant->getId();
    }
}
