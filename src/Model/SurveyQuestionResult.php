<?php

declare(strict_types=1);

namespace App\Model;

final class SurveyQuestionResult
{
    /**
     * @param array<string, int> $answersCount
     */
    public function __construct(
        public string $question,
        public array $answersCount,
    ) {
    }
}
