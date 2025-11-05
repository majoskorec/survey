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
use Doctrine\Persistence\ManagerRegistry;
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

        $toSurveys = Action::new('toAllSurveys')
            ->linkToRoute('admin_survey_index')
            ->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $toSurveys);

        $toSurveyEdit = Action::new('toSurveyEdit')
            ->linkToRoute('admin_survey_edit', ['entityId' => $surveyId])
            ->createAsGlobalAction();
        $actions->add(Crud::PAGE_INDEX, $toSurveyEdit);

        $actions->addBatchAction(
            Action::new('sendLinks')
                ->asWarningAction()
                ->linkToRoute('admin_participants_on_survey_send_links', ['surveyId' => $surveyId])
                ->setIcon('fa fa-envelope-o'),
        );

        $actions->addBatchAction(
            Action::new('addToSurvey')
                ->asDefaultAction()
                ->linkToRoute('admin_participants_on_survey_add_to_survey', ['surveyId' => $surveyId])
                ->setIcon('fa fa-user-check'),
        );

        $delete = Action::new('deleteParticipant', false, 'fa fa-trash')
            ->asDangerAction()
            ->linkToRoute(
                'admin_participants_on_survey_delete_participant',
                static fn (ParticipantOnSurveyDto $dto): array => [
                    'id' => $dto->surveyParticipantId,
                    'surveyId' => $surveyId,
                ],
            )
            ->displayIf(static fn (ParticipantOnSurveyDto $dto): bool => $dto->surveyParticipantId !== null);

        $actions->add(Crud::PAGE_INDEX, $delete);

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

    #[AdminRoute(path: '/{surveyId}/send', name: 'send_links')]
    public function sendLinks(BatchActionDto $batchActionDto, int $surveyId): Response
    {
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof ManagerRegistry);
        $entityManager = $doctrine->getManager();

        $surveyParticipantRepository = $entityManager->getRepository(SurveyParticipant::class);
        /** @var array<int> $ids */
        $ids = $batchActionDto->getEntityIds();
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

    #[AdminRoute(path: '/participant/{id}/delete', name: 'delete_participant')]
    public function deleteParticipantOnSurvey(int $id): Response
    {
        $surveyId = $this->getSurveyId();
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof ManagerRegistry);
        $entityManager = $doctrine->getManager();

        $surveyParticipant = $entityManager->getRepository(SurveyParticipant::class)->find($id);
        if ($surveyParticipant && $surveyParticipant->getSurvey()->getId() === $surveyId) {
            $entityManager->remove($surveyParticipant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_participants_on_survey_index', ['surveyId' => $this->getSurveyId()]);
    }

    #[AdminRoute(path: '/{surveyId}/add-to-survey', name: 'add_to_survey')]
    public function addToSurvey(BatchActionDto $batchActionDto, int $surveyId): Response
    {
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof ManagerRegistry);
        $entityManager = $doctrine->getManager();

        $survey = $entityManager->getRepository(Survey::class)->find($surveyId);
        if (!$survey instanceof Survey) {
            throw $this->createNotFoundException('Survey not found');
        }

        $participantRepository = $entityManager->getRepository(Participant::class);
        /** @var array<int> $ids */
        $ids = $batchActionDto->getEntityIds();
        $participants = $participantRepository->findNotInSurvey($surveyId, $ids);
        foreach ($participants as $participant) {
            $surveyParticipant = SurveyParticipant::createNew($survey, $participant);
            $entityManager->persist($surveyParticipant);
        }
        $entityManager->flush();

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
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof ManagerRegistry);
        $survey = $doctrine->getRepository(Survey::class)->find($this->getSurveyId());

        return $survey?->getTitle() ?? '';
    }
}
