<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Model\FlashType;
use App\Repository\ParticipantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

final class LinkLoginController extends AbstractController
{
    public const string ROUTE_NAME = 'app_link_login';

    public function __construct(
        private readonly NotifierInterface $notifier,
        private readonly LoginLinkHandlerInterface $loginLinkHandler,
        private readonly ParticipantRepository $userRepository,
    ) {
    }

    #[Route(path: '/link-login', name: self::ROUTE_NAME)]
    public function __invoke(Request $request): Response
    {
        // check if form is submitted
        if ($request->isMethod('POST')) {
            // load the user in some way (e.g. using the form input)
            $email = $request->request->get('_username');
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if ($user === null) {
                $this->addFlash(FlashType::DANGER->value, 'Email neexistuje!');

                return $this->render('security/request_login_link.html.twig', ['last_username' => $email]);
            }

            // create a login link for $user this returns an instance
            // of LoginLinkDetails
            $loginLinkDetails = $this->loginLinkHandler->createLoginLink($user);
            // create a notification based on the login link details
            $notification = new LoginLinkNotification(
                $loginLinkDetails,
                'Prihlásenie do trauma-kramare.sk',
            );
            $notification->content($this->createMailContent($loginLinkDetails));
            // create a recipient for this user
            $recipient = new Recipient($user->getEmail());

            // send the notification to the user
            $this->notifier->send($notification, $recipient);

            return $this->render('security/login_link_sent.html.twig');
        }

        // if it's not submitted, render the form to request the "login link"
        return $this->render('security/request_login_link.html.twig', ['last_username' => '']);
    }

    private function createMailContent(LoginLinkDetails $loginLinkDetails): string
    {
        $duration = $loginLinkDetails->getExpiresAt()->getTimestamp() - time();
        $durationString = (int) floor($duration / 60) . ' min';
        $hours = $duration / 3600;
        if ($hours >= 1.0) {
            $durationString = (int) floor($hours) . ' hod';
        }

        return sprintf(
            'Klikni na tlačidlo nižšie, aby si potvrdil, že sa chceš prihlásiť. Tento odkaz vyprší o %s.',
            $durationString,
        );
    }
}
