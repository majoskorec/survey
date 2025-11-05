<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Participant;
use App\Entity\Question;
use App\Entity\QuestionOption;
use App\Entity\Survey;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Override;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    #[Override]
    public function index(): Response
    {
        return $this->redirectToRoute('admin_user_index');
    }

    #[Override]
    public function configureDashboard(): Dashboard
    {
        $dashboard = parent::configureDashboard();
        $dashboard = $dashboard->setTitle('Survey');
        $dashboard = $dashboard->setFaviconPath('favicon.png');

        return $dashboard;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::section('User'),
            MenuItem::linkToCrud('User', 'fas fa-list', User::class),
            MenuItem::section('Participant'),
            MenuItem::linkToCrud('Participant', 'fas fa-list', Participant::class)
                ->setController(ParticipantCrudController::class),
            MenuItem::section('Survey'),
            MenuItem::linkToCrud('Survey', 'fas fa-list', Survey::class),
            MenuItem::linkToCrud('Question', 'fas fa-list', Question::class),
            MenuItem::linkToCrud('QuestionOption', 'fas fa-list', QuestionOption::class),
        ];
    }

    #[Override]
    public function configureActions(): Actions
    {
        $actions = parent::configureActions();
        $actions->add(Crud::PAGE_EDIT, Action::INDEX);
        $actions->add(Crud::PAGE_NEW, Action::INDEX);

        return $actions;
    }

    #[Override]
    public function configureAssets(): Assets
    {
        $assets = parent::configureAssets();
        $assets = $assets->addAssetMapperEntry('admin');

        return $assets;
    }

    #[Override]
    public function configureCrud(): Crud
    {
        $crud = parent::configureCrud();
        $crud = $crud->renderContentMaximized();

        return $crud;
    }
}
