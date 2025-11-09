<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Suggestion;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Override;

/**
 * @extends CrudController<Suggestion>
 */
final class SuggestionCrudController extends CrudController
{
    #[Override]
    public static function getEntityFqcn(): string
    {
        return Suggestion::class;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextareaField::new('suggestion')
                ->setFormTypeOption('empty_data', '')
                ->setRequired(true)
                ->setSortable(false)
                ->setColumns('col-12'),
            DateTimeField::new('createdAt')
                ->onlyOnIndex(),
        ];
    }
}
