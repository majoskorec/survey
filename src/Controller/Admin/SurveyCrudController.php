<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\Survey;
use App\Model\SurveyStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Override;
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
            ChoiceField::new('status')
                ->setColumns('col-12')
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
        $actions = parent::configureActions($actions);
        $actions->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER);
        $actions->add(Crud::PAGE_NEW, Action::SAVE_AND_CONTINUE);

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
}
