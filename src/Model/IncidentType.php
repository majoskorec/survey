<?php

declare(strict_types=1);

namespace App\Model;

enum IncidentType: string
{
    case GOOD = 'good';
    case NEUTRAL = 'neutral';
    case BAD = 'bad';
}
