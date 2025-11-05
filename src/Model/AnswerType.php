<?php

declare(strict_types=1);

namespace App\Model;

enum AnswerType: string
{
    case TEXT = 'text';
    case MULTIPLE_CHOICE = 'multiple_choice';
    case SINGLE_CHOICE = 'single_choice';
}
