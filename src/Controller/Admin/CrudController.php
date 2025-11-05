<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Override;

/**
 * @template TEntity of object
 * @extends AbstractCrudController<TEntity>
 */
abstract class CrudController extends AbstractCrudController
{
    #[Override]
    public function configureCrud(Crud $crud): Crud
    {
        $crud = parent::configureCrud($crud);
        $crud->showEntityActionsInlined();

        return $crud;
    }

    protected function getAdminUrlGenerator(): AdminUrlGenerator
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        /**
         * @psalm-suppress RedundantCondition
         */
        assert($adminUrlGenerator instanceof AdminUrlGenerator);

        return $adminUrlGenerator;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        $doctrine = $this->container->get('doctrine');
        assert($doctrine instanceof ManagerRegistry);

        $result = $doctrine->getManager();
        assert($result instanceof EntityManagerInterface);

        return $result;
    }
}
