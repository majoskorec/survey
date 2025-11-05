<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Question;
use App\Entity\QuestionOption;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends CrudController<Question>
 */
final class QuestionCrudController extends CrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Question::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            AssociationField::new('survey')
                ->setCrudController(SurveyCrudController::class)
                ->setTemplatePath('admin/crud/field/association_link_to_index_filter.html.twig')
                ->hideOnForm()
                ->setColumns('col-12'),
            TextareaField::new('text')
                ->setRequired(true)
                ->setFormTypeOption('empty_data', '')
                ->setSortable(false)
                ->setColumns('col-12')
                ->setNumOfRows(2),
            ChoiceField::new('answerType')
                ->setRequired(true)
                ->setColumns('col-12'),
            CollectionField::new('questionOptions')
                ->useEntryCrudForm(QuestionOptionCrudController::class)
                ->setCustomOption('parentName', 'question')
                ->setTemplatePath('admin/crud/field/collection_link_to_index_filter.html.twig')
                ->setColumns('col-12')
                ->allowAdd()
                ->allowDelete()
                ->setEntryIsComplex()
                ->setEntryToStringMethod(
                    static fn (?QuestionOption $value, TranslatorInterface $translator): string => $value?->getValue()
                        ?? 'new',
                )
                ->setFormTypeOption('by_reference', false)
                ->setFormTypeOption('attr', [
                    'data-controller' => 'orderable-collection',
                ]),
            HiddenField::new('orderIndex'),
        ];
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $adminUrlGenerator = $this->getAdminUrlGenerator();

        $editSurvey = Action::new('editSurvey', 'Edit Survey', 'fa fa-edit')
            ->linkToUrl(
                static fn (Question $entity): string => $adminUrlGenerator
                    ->unsetAll()
                    ->setController(SurveyCrudController::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($entity->getSurvey()->getId())
                    ->generateUrl(),
            );

        $actions = parent::configureActions($actions);
        $actions->disable(Action::NEW, Action::EDIT, Action::DELETE);
        $actions->add(Crud::PAGE_INDEX, $editSurvey);

        return $actions;
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);
        $filters->add('id');
        $filters->add('survey');
        $filters->add('answerType');

        return $filters;
    }
}
