<?php

declare(strict_types=1);

namespace App\Model;

use App\Entity\Question;
use InvalidArgumentException;

final class SurveyQuestionFormFieldName
{
    private const string PREFIX = 'question_';

    private function __construct(
        public readonly int $questionId,
    ) {
    }

    public static function fromName(string $name): self
    {
        $name = trim($name);
        $questionIdAsString = str_replace(self::PREFIX, '', $name);
        if (!is_numeric($questionIdAsString)) {
            throw new InvalidArgumentException('Invalid question form field name: ' . $name);
        }

        $questionId = (int) $questionIdAsString;
        if ($name !== self::toString($questionId)) {
            throw new InvalidArgumentException('Invalid question form field name: ' . $name);
        }

        return new self($questionId);
    }

    public static function fromQuestion(Question $question): self
    {
        return new self($question->getId());
    }

    public function getName(): string
    {
        return self::toString($this->questionId);
    }

    private static function toString(int $questionId): string
    {
        return sprintf('%s%d', self::PREFIX, $questionId);
    }
}
