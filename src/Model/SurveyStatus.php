<?php

declare(strict_types=1);

namespace App\Model;

enum SurveyStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case CLOSED = 'closed';
    case HIDDEN = 'hidden';

    public function asBadge(): string
    {
        $class = match ($this) {
            self::DRAFT => 'warning',
            self::PUBLISHED => 'success',
            self::CLOSED => 'secondary',
            self::HIDDEN => 'dark',
        };

        return sprintf('<span class="badge text-bg-%s">%s</span>', $class, $this->value);
    }
}
