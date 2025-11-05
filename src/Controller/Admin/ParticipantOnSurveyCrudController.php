<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Survey;
use App\Entity\SurveyParticipant;
use App\Mail\SendLinkMail;
use App\Model\ParticipantOnSurveyDto;
use App\Model\SurveyParticipantStatus;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends CrudController<Participant>
 */
#[AdminRoute(path: '/survey/participants', name: 'participants_on_survey')]
final class ParticipantOnSurveyCrudController extends CrudController
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly SendLinkMail $sendLinkMail,
    ) {
    }

    #[Override]
    public static function getEntityFqcn(): string
    {
        return Participant::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            EmailField::new('email'),
            TextField::new('name'),
            ChoiceField::new('status')->setChoices(SurveyParticipantStatus::cases()),
            TextField::new('linkToken'),
        ];
    }

    #[Override]
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $surveyId = $this->getSurveyId();
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $queryBuilder->select(sprintf(
            'new %s(entity, sp.id, sp.linkToken, sp.status)',
            ParticipantOnSurveyDto::class,
        ));
        $queryBuilder->leftJoin(
            SurveyParticipant::class,
            'sp',
            Join::WITH,
            'sp.participant = entity.id and sp.survey = :surveyId',
        );
        $queryBuilder->setParameter('surveyId', $surveyId);

        return $queryBuilder;
    }

    #[Override]
    // phpcs:ignore SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
    public function configureActions(Actions $actions): Actions
    {
        $surveyId = $this->getSurveyId();

        $actions = parent::configureActions($actions);
        $actions->disable(Action::NEW, Action::EDIT, Action::DETAIL, Action::DELETE, Action::BATCH_DELETE);
        $actions->remove(Crud::PAGE_INDEX, Action::NEW);
        $actions->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE);
        $actions->remove(Crud::PAGE_INDEX, Action::DELETE);
        $actions->remove(Crud::PAGE_INDEX, Action::EDIT);

        $toSurveyEdit = Action::new('backToSurveyEdit')
            ->linkToRoute('admin_survey_edit', ['entityId' => $surveyId])
            ->asWarningAction()
            ->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $toSurveyEdit);

        $actions->addBatchAction(
            Action::new('sendLinks')
                ->asWarningAction()
                ->linkToRoute('admin_participants_on_survey_send_batch', ['surveyId' => $surveyId])
                ->setIcon('fa fa-envelope-o'),
        );

        $actions->addBatchAction(
            Action::new('addToSurvey')
                ->asDefaultAction()
                ->linkToRoute('admin_participants_on_survey_add_batch', ['surveyId' => $surveyId])
                ->setIcon('fa fa-user-check'),
        );

        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('deleteParticipant', false, 'fa fa-trash')
                ->asDangerAction()
                ->linkToRoute(
                    'admin_participants_on_survey_delete_participant',
                    static fn (ParticipantOnSurveyDto $dto): array => [
                        'id' => $dto->surveyParticipantId,
                        'surveyId' => $surveyId,
                    ],
                )
                ->displayIf(static fn (ParticipantOnSurveyDto $dto): bool => $dto->surveyParticipantId !== null),
        );

        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('add')
                ->linkToRoute(
                    'admin_participants_on_survey_add',
                    static fn (ParticipantOnSurveyDto $dto): array => [
                        'participantId' => $dto->participant->getId(),
                        'surveyId' => $surveyId,
                    ],
                )
                ->displayIf(static fn (ParticipantOnSurveyDto $dto): bool => $dto->surveyParticipantId === null),
        );

        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('send')
                ->linkToRoute(
                    'admin_participants_on_survey_send',
                    static fn (ParticipantOnSurveyDto $dto): array => [
                        'id' => $dto->surveyParticipantId,
                        'surveyId' => $surveyId,
                    ],
                )
                ->displayIf(
                    static fn (ParticipantOnSurveyDto $dto): bool => $dto->surveyParticipantId !== null
                        && $dto->status === SurveyParticipantStatus::CREATED,
                ),
        );

        return $actions;
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);
        $filters->add('email');
        $filters->add('name');

        return $filters;
    }

    #[Override]
    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        $responseParameters->set('surveyId', $this->getSurveyId());

        return $responseParameters;
    }

    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->setPageTitle(
            Crud::PAGE_INDEX,
            sprintf('Participants on Survey `%s`', $this->getSurveyTitle()),
        );

        return $crud;
    }

    #[AdminRoute(path: '/{surveyId}/add/{participantId}', name: 'add')]
    public function add(
        #[MapEntity(id: 'surveyId')]
        Survey $survey,
        int $participantId,
    ): Response {
        return $this->addIds($survey, [$participantId]);
    }

    #[AdminRoute(path: '/{id}/send', name: 'send')]
    public function send(SurveyParticipant $surveyParticipant): Response
    {
        return $this->sendIds(
            $surveyParticipant->getSurvey()->getId(),
            [
                $surveyParticipant->getParticipant()->getId(),
            ],
        );
    }

    #[AdminRoute(path: '/{surveyId}/send-batch', name: 'send_batch')]
    public function sendBatch(BatchActionDto $batchActionDto, int $surveyId): Response
    {
        /** @var array<int> $ids */
        $ids = $batchActionDto->getEntityIds();

        return $this->sendIds($surveyId, $ids);
    }

    #[AdminRoute(path: '/participant/{id}/delete', name: 'delete_participant')]
    public function deleteParticipantOnSurvey(int $id): Response
    {
        $surveyId = $this->getSurveyId();
        $entityManager = $this->getEntityManager();

        $surveyParticipant = $entityManager->getRepository(SurveyParticipant::class)->find($id);
        if ($surveyParticipant && $surveyParticipant->getSurvey()->getId() === $surveyId) {
            $entityManager->remove($surveyParticipant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_participants_on_survey_index', ['surveyId' => $this->getSurveyId()]);
    }

    #[AdminRoute(path: '/{surveyId}/add-batch', name: 'add_batch')]
    public function addBatch(
        BatchActionDto $batchActionDto,
        #[MapEntity(id: 'surveyId')]
        Survey $survey,
    ): Response {
        /** @var array<int> $ids */
        $ids = $batchActionDto->getEntityIds();

        return $this->addIds($survey, $ids);
    }

    /**
     * @param array<int> $ids
     */
    private function addIds(Survey $survey, array $ids): Response
    {
        $entityManager = $this->getEntityManager();

        $participantRepository = $entityManager->getRepository(Participant::class);
        $participants = $participantRepository->findNotInSurvey($survey->getId(), $ids);
        foreach ($participants as $participant) {
            $surveyParticipant = SurveyParticipant::createNew($survey, $participant);
            $entityManager->persist($surveyParticipant);
        }
        $entityManager->flush();

        return $this->redirectToRoute('admin_participants_on_survey_index', ['surveyId' => $survey->getId()]);
    }

    /**
     * @param array<int> $ids
     */
    private function sendIds(int $surveyId, array $ids): Response
    {
        $entityManager = $this->getEntityManager();
        $surveyParticipantRepository = $entityManager->getRepository(SurveyParticipant::class);
        $surveyParticipants = $surveyParticipantRepository->findBySurveyIdAndParticipantIdsNotSend(
            $surveyId,
            $ids,
        );

        foreach ($surveyParticipants as $surveyParticipant) {
            $this->sendLinkMail->send($surveyParticipant);
            $surveyParticipant->send();
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_participants_on_survey_index', ['surveyId' => $surveyId]);
    }

    private function getSurveyId(): ?int
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        $surveyId = $request->attributes->get('surveyId', $request->query->get('surveyId'));
        assert(is_string($surveyId) || is_int($surveyId) || $surveyId === null);

        return $surveyId === null ? $surveyId : (int) $surveyId;
    }

    private function getSurveyTitle(): string
    {
        $entityManager = $this->getEntityManager();
        $survey = $entityManager->getRepository(Survey::class)->find($this->getSurveyId());

        return $survey?->getTitle() ?? '';
    }
}
