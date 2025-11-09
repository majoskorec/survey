<?php

declare(strict_types=1);

namespace App\Mail;

use App\Controller\Survey\ParticipantAnswerController;
use App\Entity\SurveyParticipant;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class SendLinkMail
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function send(SurveyParticipant $surveyParticipant): void
    {
        $email = $surveyParticipant->getParticipant()->getEmail();
        $name = $surveyParticipant->getParticipant()->getName();
        $message = new TemplatedEmail();
        $message->to(new Address($email, $name));
        $message->subject('Link na dotazník');
        $message->htmlTemplate('survey/mail/invite_link.html.twig');
        $link = $this->urlGenerator->generate(
            ParticipantAnswerController::ROUTE_NAME,
            ['linkToken' => $surveyParticipant->getLinkToken()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $message->context([
            'link' => $link,
            'text' => $surveyParticipant->getSurvey()->getInfoText(),
        ]);
        $this->mailer->send($message);
    }
}
