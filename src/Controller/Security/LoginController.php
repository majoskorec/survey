<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Controller\DefaultController;
use App\Model\FlashType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class LoginController extends AbstractController
{
    public const string ROUTE_NAME = 'app_login';

    #[Route(path: '/login', name: self::ROUTE_NAME)]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_user_index');
        }

        if ($this->isGranted('ROLE_USER')) {
            $this->addFlash(
                FlashType::DANGER->value,
                'Nie ste administrátor. Prístup do administrácie Vám bol zamietnutý.',
            );

            return $this->redirectToRoute(DefaultController::ROUTE_NAME);
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'csrf_token_intention' => 'csrf-token',
            'error' => $error,
            'last_username' => $lastUsername,
            'target_path' => $this->generateUrl('admin_user_index'),
        ]);
    }
}
