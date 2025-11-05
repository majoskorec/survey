<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SurveyParticipant;
use App\Model\SurveyParticipantStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurveyParticipant>
 */
final class SurveyParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyParticipant::class);
    }

    public function findBySurveyIdAndParticipantId(int $surveyId, int $participantId): ?SurveyParticipant
    {
        return $this->findOneBy(['survey' => $surveyId, 'participant' => $participantId]);
    }

    /**
     * @param array<int> $participantIds
     * @return array<SurveyParticipant>
     */
    public function findBySurveyIdAndParticipantIdsNotSend(int $surveyId, array $participantIds): array
    {
        return $this->findBy([
            'participant' => $participantIds,
            'status' => SurveyParticipantStatus::CREATED,
            'survey' => $surveyId,
        ]);
    }
}
