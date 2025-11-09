<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Survey;
use App\Model\FlashType;
use App\Model\SurveyStatus;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends CrudController<Survey>
 */
final class SurveyCrudController extends CrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Survey::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function createEntity(string $entityFqcn)
    {
        $entity = new Survey();
        $entity->setStatus(SurveyStatus::DRAFT);

        return $entity;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title')
                ->setFormTypeOption('empty_data', '')
                ->setColumns('col-12'),
            SlugField::new('slug')
                ->setFormTypeOption('empty_data', '')
                ->setColumns('col-12')
                ->setTargetFieldName('title')
                ->setUnlockConfirmationMessage(
                    'It is highly recommended to use the automatic slugs, but you can customize them',
                ),
            TextareaField::new('infoText')
                ->setFormTypeOption('empty_data', '')
                ->setColumns('col-12'),
            ChoiceField::new('status')
                ->setColumns('col-12')
                ->formatValue(static fn (SurveyStatus $status): string => $status->asBadge())
                ->hideWhenCreating(),
            CollectionField::new('questions')
                ->useEntryCrudForm(QuestionCrudController::class)
                ->setCustomOption('parentName', 'survey')
                ->setTemplatePath('admin/crud/field/collection_link_to_index_filter.html.twig')
                ->setColumns('col-12')
                ->allowAdd()
                ->allowDelete()
                ->renderExpanded()
                ->setEntryIsComplex()
                ->setEntryToStringMethod(
                    static fn (?Question $value, TranslatorInterface $translator): string => $value?->getText()
                        ?? 'new',
                )
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('attr', [
                    'data-controller' => 'orderable-collection',
                ]),
            CollectionField::new('surveyParticipants', 'Participants')
                ->setTemplatePath('admin/crud/field/survey_participants_collection.html.twig')
                ->setColumns('col-12')
                ->onlyOnIndex(),
        ];
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $manageParticipants = Action::new('manageParticipants')
            ->setLabel('Manage Participants')
            ->linkToRoute(
                'admin_participants_on_survey_index',
                static fn (Survey $survey): array => ['surveyId' => $survey->getId()],
            );

        $actions = parent::configureActions($actions);
        $actions->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);
        $actions->add(Crud::PAGE_INDEX, $manageParticipants);
        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('publish')
                ->asPrimaryAction()
                ->displayIf(static fn (Survey $survey): bool => $survey->getStatus() === SurveyStatus::DRAFT)
                ->linkToCrudAction('publishSurvey'),
        );
        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('close')
                ->asDangerAction()
                ->displayIf(static fn (Survey $survey): bool => $survey->getStatus() === SurveyStatus::PUBLISHED)
                ->linkToCrudAction('closeSurvey'),
        );
        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('hide')
                ->asDangerAction()
                ->displayIf(static fn (Survey $survey): bool => $survey->getStatus() === SurveyStatus::CLOSED)
                ->linkToCrudAction('hideSurvey'),
        );
        $actions->add(Crud::PAGE_EDIT, $manageParticipants);

        return $actions;
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);
        $filters->add('id');
        $filters->add('title');
        $filters->add('status');

        return $filters;
    }

    #[AdminRoute(path: '/{entityId}/close', name: 'publish_close')]
    public function closeSurvey(
        #[MapEntity(id: 'entityId')]
        Survey $survey,
    ): Response {
        $survey->close();

        $entityManager = $this->getEntityManager();
        $entityManager->flush();

        $this->addFlash(FlashType::SUCCESS->value, 'Survey was closed successfully.');

        return $this->redirectToRoute('admin_survey_index');
    }

    #[AdminRoute(path: '/{entityId}/hide', name: 'publish_hide')]
    public function hideSurvey(
        #[MapEntity(id: 'entityId')]
        Survey $survey,
    ): Response {
        $survey->hide();

        $entityManager = $this->getEntityManager();
        $entityManager->flush();

        $this->addFlash(FlashType::SUCCESS->value, 'Survey was hidden successfully.');

        return $this->redirectToRoute('admin_survey_index');
    }

    #[AdminRoute(path: '/{entityId}/publish', name: 'publish_survey')]
    public function publishSurvey(
        #[MapEntity(id: 'entityId')]
        Survey $survey,
    ): Response {
        $survey->publish();

        $entityManager = $this->getEntityManager();
        $entityManager->flush();

        $this->addFlash(FlashType::SUCCESS->value, 'Survey was published successfully.');

        return $this->redirectToRoute('admin_survey_index');
    }
}
