<?php

declare(strict_types=1);

namespace App\Model;

enum SurveyParticipantStatus: string
{
    case CREATED = 'created';
    case SENT = 'sent';
    case COMPLETED = 'completed';
}
