<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Participant;
use App\Entity\SurveyParticipant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
final class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * @param array<int>|null $ids
     * @return array<Participant>
     */
    public function findNotInSurvey(int $surveyId, ?array $ids): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin(
            SurveyParticipant::class,
            'sp',
            Join::WITH,
            'sp.participant = p AND sp.survey = :surveyId',
        );
        $queryBuilder->andWhere('sp.id is null');
        $queryBuilder->setParameter('surveyId', $surveyId);
        if ($ids !== null) {
            $queryBuilder->andWhere('p.id IN (:ids)');
            $queryBuilder->setParameter('ids', $ids);
        }
        /** @var array<Participant> $result */
        $result = $queryBuilder->getQuery()->getResult();

        return $result;
    }
}
