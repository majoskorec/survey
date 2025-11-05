<?php

declare(strict_types=1);

namespace App\Survey;

use App\Entity\Question;
use App\Entity\Survey;
use App\Entity\SurveyParticipant;
use App\Model\SurveyQuestionFormFieldName;
use App\Model\SurveyResult;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;

final class ResultFactory
{
    public function create(Survey $survey): SurveyResult
    {
        $invited = 0;
        $answers = 0;
        $surveyQuestionResultsBuilder = new SurveyQuestionResultsBuilder();
        foreach ($survey->getSurveyParticipants() as $surveyParticipant) {
            $this->processSurveyParticipant(
                $surveyQuestionResultsBuilder,
                $surveyParticipant,
                $invited,
                $answers,
            );
        }
        $this->addEmptyAnswers($surveyQuestionResultsBuilder, $survey);

        return new SurveyResult(
            $invited,
            $answers,
            $surveyQuestionResultsBuilder->build(),
        );
    }

    private function processSurveyParticipant(
        SurveyQuestionResultsBuilder $surveyQuestionResultsBuilder,
        SurveyParticipant $surveyParticipant,
        int &$invited,
        int &$answers,
    ): void {
        $invited++;
        $participantAnswers = $surveyParticipant->getAnswers();
        if ($participantAnswers === null) {
            return;
        }
        $answers++;
        foreach ($participantAnswers as $questionName => $participantAnswer) {
            $this->processParticipantAnswer(
                $questionName,
                $surveyParticipant->getSurvey()->getQuestions(),
                $surveyQuestionResultsBuilder,
                $participantAnswer,
            );
        }
    }

    /**
     * @param Collection<int, Question> $questions
     * @param string|array<string>|null $participantAnswer
     */
    private function processParticipantAnswer(
        string $questionName,
        Collection $questions,
        SurveyQuestionResultsBuilder $surveyQuestionResultsBuilder,
        string|array|null $participantAnswer,
    ): void {
        if ($participantAnswer === null) {
            return;
        }
        try {
            $name = SurveyQuestionFormFieldName::fromName($questionName);
        } catch (InvalidArgumentException) {
            // @todo log
            return;
        }

        $question = $questions->get($name->questionId);
        if ($question === null) {
            // @todo log
            return;
        }

        $surveyQuestionResultsBuilder->addAnswer($question, $participantAnswer);
    }

    private function addEmptyAnswers(SurveyQuestionResultsBuilder $questionResultsBuilder, Survey $survey): void
    {
        foreach ($survey->getQuestions() as $question) {
            foreach ($question->getQuestionOptions() as $questionOption) {
                $questionResultsBuilder->addEmptyAnswerOption($questionOption);
            }
        }
    }
}
