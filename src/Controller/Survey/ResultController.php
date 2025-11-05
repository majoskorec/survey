<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use App\Entity\Survey;
use App\Survey\ResultFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ResultController extends AbstractController
{
    public const string ROUTE_NAME = 'app_survey_result';

    public function __construct(
        private readonly ResultFactory $resultFactory,
    ) {
    }

    #[Route(path: '/vysledky/{slug:survey}', name: self::ROUTE_NAME)]
    public function index(
        Survey $survey,
    ): Response {
        $result = $this->resultFactory->create($survey);

        return $this->render('survey/result/index.html.twig', [
            'result' => $result,
            'survey' => $survey,
        ]);
    }
}
