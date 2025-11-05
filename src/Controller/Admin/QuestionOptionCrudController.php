<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\QuestionOption;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Override;

/**
 * @extends CrudController<QuestionOption>
 */
final class QuestionOptionCrudController extends CrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return QuestionOption::class;
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
            AssociationField::new('question.survey', 'Survey')
                ->setCrudController(SurveyCrudController::class)
                ->setTemplatePath('admin/crud/field/association_link_to_index_filter.html.twig')
                ->hideOnForm()
                ->setColumns('col-12'),
            AssociationField::new('question')
                ->setCrudController(QuestionCrudController::class)
                ->setTemplatePath('admin/crud/field/association_link_to_index_filter.html.twig')
                ->hideOnForm()
                ->setColumns('col-12'),
            TextareaField::new('label')
                ->setFormTypeOption('empty_data', '')
                ->setRequired(true)
                ->setColumns('col-12')
                ->setNumOfRows(2),
            TextField::new('value')
                ->setFormTypeOption('empty_data', '')
                ->setRequired(true)
                ->setColumns('col-12'),
            HiddenField::new('orderIndex'),
        ];
    }

    #[Override]
    public function configureActions(Actions $actions): Actions
    {
        $adminUrlGenerator = $this->getAdminUrlGenerator();

        $editSurvey = Action::new('editSurvey', 'Edit Survey', 'fa fa-edit')
            ->linkToUrl(
                static fn (QuestionOption $entity): string => $adminUrlGenerator
                    ->unsetAll()
                    ->setController(SurveyCrudController::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($entity->getQuestion()->getSurvey()->getId())
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
        $filters->add(EntityFilter::new('question'));

        return $filters;
    }
}
