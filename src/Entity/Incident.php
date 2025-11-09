<?php

declare(strict_types=1);

namespace App\Entity;

use App\Model\IncidentType;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'incident')]
class Incident extends AdminEntity
{
    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id', nullable: false)]
    private Participant $participant;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $occurredAt;

    #[ORM\Column(type: Types::TEXT, nullable: false)]
    private string $description;

    #[ORM\Column(type: Types::STRING, nullable: false, enumType: IncidentType::class)]
    private IncidentType $incidentType;

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function setOccurredAt(DateTimeImmutable $occurredAt): void
    {
        $this->occurredAt = $occurredAt;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getIncidentType(): IncidentType
    {
        return $this->incidentType;
    }

    public function setIncidentType(IncidentType $incidentType): void
    {
        $this->incidentType = $incidentType;
    }

    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    public function setParticipant(Participant $participant): void
    {
        $this->participant = $participant;
    }
}
