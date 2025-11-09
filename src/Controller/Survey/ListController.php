<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use App\Controller\Controller;
use App\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ListController extends Controller
{
    public const string ROUTE_NAME = 'app_survey_list';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/dotazniky', name: self::ROUTE_NAME)]
    public function __invoke(): Response
    {
        $surveys = $this->entityManager->getRepository(Survey::class)->findForList();

        return $this->render('survey/list/index.html.twig', [
            'surveys' => $surveys,
        ]);
    }
}
