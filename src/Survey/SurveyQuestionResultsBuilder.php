<?php

declare(strict_types=1);

namespace App\Survey;

use App\Entity\Question;
use App\Entity\QuestionOption;
use App\Model\SurveyQuestionResult;

/**
 * @psalm-type QuestionData = array{
 *      question: string,
 *      answersCount: array<string, int>,
 *  }
 */
final class SurveyQuestionResultsBuilder
{
    /**
     * @var array<int, QuestionData>
     */
    private array $results;

    public function __construct()
    {
        $this->results = [];
    }

    /**
     * @param string|array<string> $participantAnswer
     */
    public function addAnswer(Question $question, string|array $participantAnswer): void
    {
        $questionData = $this->results[$question->getId()]
            ?? $this->createEmptyQuestionData($question);

        $participantAnswers = is_array($participantAnswer) ? $participantAnswer : [$participantAnswer];
        foreach ($participantAnswers as $stringAnswer) {
            $questionData = $this->addStringAnswerToQuestionData($question, $questionData, $stringAnswer);
        }

        $this->results[$question->getId()] = $questionData;
    }

    public function addEmptyAnswerOption(QuestionOption $questionOption): void
    {
        $question = $questionOption->getQuestion();
        $questionLabel = $question->getText();
        $questionData = $this->results[$question->getId()]
            ?? $this->createEmptyQuestionData($question);
        $count = $questionData['answersCount'][$questionLabel] ?? 0;
        $questionData['answersCount'][$questionLabel] = $count;

        $this->results[$question->getId()] = $questionData;
    }

    /**
     * @return array<int, SurveyQuestionResult>
     */
    public function build(): array
    {
        return array_map(
            static fn (array $questionData): SurveyQuestionResult => new SurveyQuestionResult(
                question: $questionData['question'],
                answersCount: $questionData['answersCount'],
            ),
            $this->results,
        );
    }

    /**
     * @return QuestionData
     */
    private function createEmptyQuestionData(Question $question): array
    {
        return [
            'answersCount' => [],
            'question' => $question->getText(),
        ];
    }

    /**
     * @param QuestionData $questionData
     * @return QuestionData
     */
    private function addStringAnswerToQuestionData(Question $question, array $questionData, string $answer): array
    {
        $answerText = $this->findAnswerText($question, $answer);

        $answerCount = $questionData['answersCount'][$answerText] ?? 0;
        $questionData['answersCount'][$answerText] = $answerCount + 1;

        return $questionData;
    }

    private function findAnswerText(Question $question, string $answer): string
    {
        foreach ($question->getQuestionOptions() as $questionOption) {
            if ($questionOption->getValue() !== $answer) {
                continue;
            }

            return $questionOption->getLabel();
        }

        return $answer;
    }
}
