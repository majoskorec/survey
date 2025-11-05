<?php

declare(strict_types=1);

namespace App\Model;

enum SurveyStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
}
