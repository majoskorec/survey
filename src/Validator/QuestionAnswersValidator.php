<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Question;
use App\Model\AnswerType;
use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class QuestionAnswersValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof QuestionAnswers) {
            throw new UnexpectedTypeException($constraint, QuestionAnswers::class);
        }

        if (!$value instanceof Question) {
            throw new UnexpectedValueException($value, Question::class);
        }

        match ($value->getAnswerType()) {
            AnswerType::MULTIPLE_CHOICE, AnswerType::SINGLE_CHOICE => $this->validateAtLeast2Choice(
                $value,
                $constraint,
            ),
            AnswerType::TEXT => $this->validateNoChoices($value, $constraint),
        };
    }

    private function validateNoChoices(Question $value, QuestionAnswers $constraint): void
    {
        if ($value->getQuestionOptions()->count() === 0) {
            return;
        }

        $this->context->buildViolation($constraint->getMessage($value->getAnswerType()))
            ->setParameter('{{ type }}', $value->getAnswerType()->value)
            ->atPath('answerType')
            ->addViolation();
    }

    private function validateAtLeast2Choice(Question $value, QuestionAnswers $constraint): void
    {
        if ($value->getQuestionOptions()->count() >= 2) {
            return;
        }

        $this->context->buildViolation($constraint->getMessage($value->getAnswerType()))
            ->setParameter('{{ type }}', $value->getAnswerType()->value)
            ->atPath('answerType')
            ->addViolation();
    }
}
