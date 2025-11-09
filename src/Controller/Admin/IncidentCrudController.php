<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Incident;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Override;

/**
 * @extends CrudController<Incident>
 */
final class IncidentCrudController extends CrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Incident::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('participant')
                ->setCrudController(ParticipantCrudController::class)
                ->setRequired(true)
                ->setFormTypeOption('choice_label', 'name')
                ->setFormTypeOption('required', true)
                ->setSortable(false)
                ->setSortProperty('name')
                ->setColumns('col-12'),
            TextareaField::new('description')
                ->setFormTypeOption('empty_data', '')
                ->setRequired(true)
                ->setSortable(false)
                ->setColumns('col-12'),
            DateField::new('occurredAt')
                ->setRequired(true),
            ChoiceField::new('incidentType')
                ->setColumns('col-12')
                ->setFormTypeOption('required', true)
                ->setRequired(true),
        ];
    }

    #[Override]
    public function configureFilters(Filters $filters): Filters
    {
        $filters = parent::configureFilters($filters);
        $filters->add('id');
        $filters->add('participant');
        $filters->add('occurredAt');

        return $filters;
    }
}
