<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\StudentAnswer;
use App\Entity\StudentCopy;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StudentAnswerController extends AbstractApiController
{
    /**
     * This route's purpose is to create Text answers to a question.
     * Multiple choice questions canno't be answered here.
     *
     * @param User $user
     * @param Request $request
     * @param StudentCopy $studentCopy
     * @param Question $question
     * @param ApiRequestValidator $apiRequestValidator
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @return Response
     */
    #[Route('/api/studentCopy/{id}/question/{question_id}/answers', name: 'app_student_answer_text', methods: ['POST'])]
    public function createTextAnswer(
                            #[CurrentUser] User $user,
                            Request $request,
                            StudentCopy $studentCopy,
                            #[MapEntity(expr: 'repository.find(question_id)')]
                            Question $question,
                            ApiRequestValidator $apiRequestValidator,
                            EntityManagerInterface $entityManager,
                            ValidatorInterface $validator,
                            SerializerInterface $serializer,
    ): Response
    {
        // Only students can add answers to their copies.
        if ($studentCopy->getStudent() !== $user) {
            throw new AccessDeniedException();
        }

        $this->checkEvaluationAvailable($studentCopy->getEvaluation());

        // TODO : Use answer count instead of isQcm()
        // MCQ can not be answered in this route
        if ($question->isIsQcm()) {
            throw new BadRequestHttpException();
        }

        /** @var StudentAnswer $studentAnswer */
        $studentAnswer = $apiRequestValidator->checkRequestValidity($request, StudentAnswer::class);

        $studentAnswer->setQuestion($question);
        $studentAnswer->setStudentCopy($studentCopy);

        $studentCopy->addStudentAnswer($studentAnswer);

        $errors = $validator->validate($studentAnswer);

        if (count($errors) > 0) {
            throw new JsonException($errors);
        }

        $entityManager->persist($studentAnswer);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($studentAnswer, 'json', [ 'groups' => 'fetchAnswer' ]));
    }

    #[Route('/api/studentCopy/{id}/question/answers/{answer_id}', name: 'app_student_answer_qcm', methods: ['POST'])]
    public function createQcmAnswer(
        #[CurrentUser] User $user,
        StudentCopy $studentCopy,
        #[MapEntity(expr: 'repository.find(answer_id)')]
        Answer $answer,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
    ): Response
    {
        // Only students can add answers to their copies.
        if ($studentCopy->getStudent() !== $user) {
            throw new AccessDeniedException();
        }

        $this->checkEvaluationAvailable($studentCopy->getEvaluation());

        $question = $answer->getQuestion();
        // MCQ can not be answered in this route
        if (!$question->isIsQcm()) {
            throw new BadRequestHttpException();
        }

        $studentAnswer = new StudentAnswer();

        $studentAnswer->setStudentCopy($studentCopy);
        $studentAnswer->setQuestion($question);

        $errors = $validator->validate($studentAnswer);

        if (count($errors) > 0) {
            throw new JsonException($errors);
        }

        $entityManager->persist($studentAnswer);
        $entityManager->flush();

        return $this->json($studentAnswer, context: [ 'groups' => 'fetchAnswer']);
    }

    public function checkEvaluationAvailable(Evaluation $evaluation)
    {
        // Today's date.
        $dateNow = new \DateTimeImmutable();

        if ($evaluation->getStartsAt() > $dateNow || $evaluation->getEndsAt() < $dateNow) {
            throw new BadRequestHttpException('Evaluation can only be modified whithin its time boundaries.');
        }
    }
}
