<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use App\Controller\DefaultController;
use App\Entity\SurveyParticipant;
use App\Model\FlashType;
use App\Survey\ParticipantAnswerFormFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @psalm-import-type AnswerData from SurveyParticipant
 */
final class ParticipantAnswerController extends AbstractController
{
    public const string ROUTE_NAME = 'app_survey_participant_answer';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ParticipantAnswerFormFactory $participantAnswerFormFactory,
    ) {
    }

    #[Route(path: '/dotaznik/{linkToken}', name: self::ROUTE_NAME)]
    public function __invoke(Request $request, string $linkToken): Response
    {
        $surveyParticipant = $this->entityManager->getRepository(SurveyParticipant::class)
            ->findOneBy(['linkToken' => $linkToken]);
        if ($surveyParticipant === null) {
            $this->addFlash(FlashType::DANGER->value, 'Neplatný odkaz na dotazník.');

            return $this->redirectToRoute(DefaultController::ROUTE_NAME);
        }
        if (!$surveyParticipant->canEdit()) {
            $this->addFlash(FlashType::DANGER->value, 'Dotazník už nieje možné upravovať.');

            return $this->redirectToRoute(DefaultController::ROUTE_NAME);
        }

        $form = $this->participantAnswerFormFactory->create($surveyParticipant);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // phpcs:ignore SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
            /** @var AnswerData $data */
            $data = $form->getData();
            $surveyParticipant->complete($data);
            $this->entityManager->flush();

            $this->addFlash(FlashType::SUCCESS->value, 'Ďakujeme za vyplnenie dotazníka.');

            return $this->redirectToRoute(DefaultController::ROUTE_NAME);
        }

        return $this->render('survey/answer/index.html.twig', [
            'form' => $form,
            'survey' => $surveyParticipant->getSurvey(),
        ]);
    }
}
