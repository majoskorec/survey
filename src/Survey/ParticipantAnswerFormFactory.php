<?php

declare(strict_types=1);

namespace App\Survey;

use App\Entity\Question;
use App\Entity\SurveyParticipant;
use App\Model\AnswerType;
use App\Model\SurveyQuestionFormFieldName;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * @psalm-import-type AnswerData from SurveyParticipant
 */
final class ParticipantAnswerFormFactory
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * @return FormInterface<AnswerData|null>
     */
    public function create(SurveyParticipant $surveyParticipant): FormInterface
    {
        /**
         * @var FormBuilderInterface<AnswerData|null> $formBuilder
         */
        $formBuilder = $this->formFactory->createBuilder();
        $formBuilder = $formBuilder->setData($this->createData($surveyParticipant));
        $survey = $surveyParticipant->getSurvey();
        foreach ($survey->getQuestions() as $question) {
            $formBuilder = match ($question->getAnswerType()) {
                AnswerType::TEXT => $this->createTextAnswer($formBuilder, $question),
                AnswerType::SINGLE_CHOICE => $this->createSingleChoiceAnswer($formBuilder, $question),
                AnswerType::MULTIPLE_CHOICE => $this->createMultipleChoiceAnswer($formBuilder, $question),
            };
        }
        /** @var FormBuilderInterface<AnswerData|null> $formBuilder */
        $formBuilder = $formBuilder->add('submit', SubmitType::class, ['label' => 'Odošli']);

        return $formBuilder->getForm();
    }

    /**
     * @return AnswerData|null
     */
    private function createData(SurveyParticipant $surveyParticipant): ?array
    {
        return $surveyParticipant->getAnswers();
    }

    /**
     * @param FormBuilderInterface<AnswerData|null> $formBuilder
     * @return FormBuilderInterface<AnswerData|null>
     */
    private function createTextAnswer(
        FormBuilderInterface $formBuilder,
        Question $question,
    ): FormBuilderInterface {
        $name = SurveyQuestionFormFieldName::fromQuestion($question);
        $formBuilder->add($name->getName(), TextareaType::class, [
            'label' => $question->getText(),
            'required' => true,
        ]);

        return $formBuilder;
    }

    /**
     * @param FormBuilderInterface<AnswerData|null> $formBuilder
     * @return FormBuilderInterface<AnswerData|null>
     */
    private function createSingleChoiceAnswer(
        FormBuilderInterface $formBuilder,
        Question $question,
    ): FormBuilderInterface {
        $choices = $this->createChoices($question);
        $name = SurveyQuestionFormFieldName::fromQuestion($question);
        $formBuilder->add($name->getName(), ChoiceType::class, [
            'choices' => $choices,
            'expanded' => true,
            'label' => $question->getText(),
            'multiple' => false,
            'required' => true,
        ]);

        return $formBuilder;
    }

    /**
     * @param FormBuilderInterface<AnswerData|null> $formBuilder
     * @return FormBuilderInterface<AnswerData|null>
     */
    private function createMultipleChoiceAnswer(
        FormBuilderInterface $formBuilder,
        Question $question,
    ): FormBuilderInterface {
        $choices = $this->createChoices($question);
        $name = SurveyQuestionFormFieldName::fromQuestion($question);
        $formBuilder->add($name->getName(), ChoiceType::class, [
            'choices' => $choices,
            'expanded' => true,
            'label' => $question->getText(),
            'multiple' => true,
            'required' => true,
        ]);

        return $formBuilder;
    }

    /**
     * @return array<string, string>
     */
    private function createChoices(Question $question): array
    {
        $choices = [];
        foreach ($question->getQuestionOptions() as $questionOption) {
            $choices[$questionOption->getLabel()] = $questionOption->getValue();
        }

        return $choices;
    }
}
