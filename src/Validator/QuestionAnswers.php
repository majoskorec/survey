<?php

declare(strict_types=1);

namespace App\Validator;

use App\Model\AnswerType;
use Attribute;
use Override;
use Symfony\Component\Validator\Constraint;

#[Attribute]
final class QuestionAnswers extends Constraint
{
    public string $messageAtLeast2 = 'Otázka typu "{{ type }}" musí mať aspon 2 odpovede.';
    public string $messageNoChoices = 'Otázka typu "{{ type }}" nesmie mať žiadnu odpoveď.';

    #[Override]
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function getMessage(AnswerType $answerType): string
    {
        return match ($answerType) {
            AnswerType::MULTIPLE_CHOICE, AnswerType::SINGLE_CHOICE => $this->messageAtLeast2,
            AnswerType::TEXT => $this->messageNoChoices,
        };
    }
}
