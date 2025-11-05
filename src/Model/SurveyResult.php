<?php

declare(strict_types=1);

namespace App\Model;

final class SurveyResult
{
    /**
     * @param array<SurveyQuestionResult> $results
     */
    public function __construct(
        public readonly int $invited,
        public readonly int $answers,
        public readonly array $results,
    ) {
    }
}
