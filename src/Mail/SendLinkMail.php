<?php

declare(strict_types=1);

namespace App\Mail;

use App\Controller\Survey\ParticipantAnswerController;
use App\Entity\SurveyParticipant;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
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
        $message = new Email();
        $message->to($email);
        $message->subject('Your survey link');
        $link = $this->urlGenerator->generate(
            ParticipantAnswerController::ROUTE_NAME,
            ['linkToken' => $surveyParticipant->getLinkToken()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );
        $message->text($link);
        $this->mailer->send($message);
    }
}
