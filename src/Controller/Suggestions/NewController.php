<?php

declare(strict_types=1);

namespace App\Controller\Suggestions;

use App\Controller\Controller;
use App\Entity\Suggestion;
use App\Form\Type\SuggestionType;
use App\Model\FlashType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NewController extends Controller
{
    public const string ROUTE_NAME = 'app_suggestions_new';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/podnety', name: self::ROUTE_NAME)]
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(SuggestionType::class);
        $form->add('submit', SubmitType::class, [
            'label' => 'Odoslať podnet',
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();
            assert($entity instanceof Suggestion);
            $this->entityManager->persist($entity);
            $this->entityManager->flush();

            $this->addFlash(FlashType::SUCCESS->value, 'Podnet bol úspešne odoslaný. Ďakujeme za vašu spätnú väzbu!');

            return $this->redirectToRoute(self::ROUTE_NAME);
        }

        return $this->render('suggestion/new.html.twig', [
            'form' => $form,
        ]);
    }
}
