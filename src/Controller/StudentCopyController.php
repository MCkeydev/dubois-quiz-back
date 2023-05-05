<?php

namespace App\Controller;

use App\DTO\CreateStudentAnswerDTO;
use App\DTO\GradeCopyDTO;
use App\Entity\Answer;
use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\StudentAnswer;
use App\Entity\StudentCopy;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\expr;

class StudentCopyController extends AbstractApiController
{

    /**
     * Route to submitStudentCopy, and create all the student answers
     */
    #[Route('/api/evaluation/{id}/studentCopy/submit', name: 'app_student_copy_submit', methods: ['POST'])]
    public function submitStudentCopy(
        #[CurrentUser] User    $user,
        Request                $request,
        Evaluation             $evaluation,
        EntityManagerInterface $entityManager,
        ApiRequestValidator    $apiRequestValidator,
        ValidatorInterface     $validator,
    )
    {
        // Before any further processing, we must make sure that the user is allowed on the evaluation
        // Fetches all the formations allowed on the evaluation
        $formation = $evaluation->getFormation();

        // Checks if the user is a part of the formation
        if (!$formation->getUsers()->contains($user)) {
            throw new AccessDeniedException();
        }

        // Here me want to create studentCopy, and each of its studentAnswers in one take

        // Decodes the json to allow iterating the request content
        $requestArray = json_decode($request->getContent(), true);

        // Stores all the evalution's questions
        $questions = $evaluation->getQuiz()->getQuestions();

        $dtoArray = [];

        /**
         * Validates each of the request's array elements to match the DTO
         * Now that we have an array of DTO's, we want to make sure that the question is in fact,
         * related to the evaluation.
         */
        foreach ($requestArray as $_ => $value) {
            // Checks the type and structure of request and instantiates a dto
            $dtoArray[] = $apiRequestValidator->checkRequestValidity(json_encode($value), CreateStudentAnswerDTO::class, isArray: true);
        }

        // If the student copy already exists we use it, otherwise we create it
        $studentCopy = $entityManager->getRepository(StudentCopy::class)->findOneBy([ 'student' => $user, 'evaluation' => $evaluation ]);

        // If no studentCopy was found, we create one
        if (null === $studentCopy) {
            $studentCopy = (new StudentCopy())->setEvaluation($evaluation)->setStudent($user);
            $entityManager->persist($studentCopy);
        }

        // TODO : comment this, and throw errors

        // Loops over all the dtos to create corresponding student answers
        /**
         * @var CreateStudentAnswerDTO $dto
         */
        foreach ($dtoArray as $_ => $dto) {
            // If the request's question is present in the evaluation's question, we can continue
            if ($question = $questions->findFirst(function (int $key, Question $value) use ($dto) {
                return $value->getId() === $dto->question;
            })) {
                // Gets all the answers of the question
                $answers = $question->getAnswers();
                // If there is more than one, and one corresponds to the choice in the dto
                if ($answers->count() > 1 && $dto->choice && $answer = $answers->findFirst(function (int $key, Answer $value) use ($dto) {
                        return $value->getId() === $dto->choice;
                    })) {
                    $studentAnswer = (new StudentAnswer())
                        ->setStudentCopy($studentCopy)
                        ->setQuestion($question)
                        ->setChoice($answer);
                } else if ($dto->answer) {
                    $studentAnswer = (new StudentAnswer())
                        ->setStudentCopy($studentCopy)
                        ->setQuestion($question)
                        ->setAnswer($dto->answer);

                }

                $errors = $validator->validate($studentAnswer);

                if (count($errors) > 0) {
                    /*
                     * Uses a __toString method on the $errors variable which is a
                     * ConstraintViolationList object. This gives us a nice string
                     * for debugging.
                     */
                    $errorsString = (string) $errors;

                    return new Response($errorsString, Response::HTTP_BAD_REQUEST);
                }

                $entityManager->persist($studentAnswer);
            };
        }

        $studentCopy->setIsLocked(true);
        $entityManager->flush();

        return $this->json($studentCopy, context: ['groups' => 'fetchStudentCopy']);
    }

    /**
     * Route to create a student copy object.
     * Only allowed on students.
     * @param User $user
     * @param Request $request
     * @param Evaluation $evaluation
     * @param ApiRequestValidator $apiRequestValidator
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @return Response
     * @throws \JsonException
     */
//    #[Route('/api/evaluation/{id}/studentCopy', name: 'app_student_copy_create', methods: ['POST'])]
//    public function createStudentCopy (
//        #[CurrentUser] User $user,
//        Request $request,
//        Evaluation $evaluation,
//        ApiRequestValidator $apiRequestValidator,
//        EntityManagerInterface $entityManager,
//        SerializerInterface $serializer,
//        ValidatorInterface $validator,
//    ): Response
//    {
//        // TODO: optimize this (no need to run validation twice)
//
//        // Checks if this the user is a student.
//        $this->denyAccessUnlessGranted('ROLE_ELEVE');
//
//        /** Checking user is allowed */
//
//        // Fetches all the formations corresponding to the evaluation
//        $formation = $evaluation->getFormation();
//
//        // Checks if the user is a part of the formation
//        if (!$formation->getUsers()->contains($user)) {
//            throw new AccessDeniedException();
//        }
//
//        $dateNow =  new \DateTimeImmutable();
//
//        /** Checking the timeframe is not over */
//        if ($evaluation->getStartsAt() > $dateNow || $dateNow > $evaluation->getEndsAt()) {
//            throw new \JsonException('The evaluation can only be accessed within its time frame');
//        }
//
//        /**
//         * @var StudentCopy $studentCopy
//         */
//        $studentCopy = $apiRequestValidator->checkRequestValidity($request, StudentCopy::class);
//
//        $studentCopy->setStudent($user);
//        $studentCopy->setProfessor($evaluation->getAuthor());
//        $studentCopy->setEvaluation($evaluation);
//
//        $errors = $validator->validate($studentCopy);
//
//        if (count($errors) > 0) {
//            throw new JsonException($errors);
//        }
//
//        $entityManager->persist($studentCopy);
//        $entityManager->flush();
//
//        return new JsonResponse($serializer->serialize($studentCopy, 'json', [ 'groups' => 'fetchStudentCopy' ]), Response::HTTP_CREATED);
//    }

    /**
     * Calculates the average score of an evaluation.
     *
     * @param EntityManagerInterface $entityManager
     * @param StudentCopy $copy
     * @return bool
     */
    private function avegareCopyScore(ReadableCollection $gradedCopies): bool
    {
            $average = 0;
            /**
            * @var StudentCopy $value
            */
            foreach ($gradedCopies->toArray() as $_ => $value) {
                $average += $value->getScore();
            }

            return $average / $gradedCopies->count();
    }


    /**
     * Computes the position of each copy, and sets it
     *
     * @param ReadableCollection $gradedCopies Collection of copies with a non-null score
     * @return void
     */
    private function computeBillboard(ReadableCollection $gradedCopies): void
    {
        // Gets all the copies in array form
        $copies = $gradedCopies->getValues();

        // Sorts in DESC order
        usort($copies, function($a, $b)  {
                return $b->getScore() <=> $a->getScore();
        });

        // Sets the position as the copy index + 1
        /**
         * @var StudentCopy $value
         */
        foreach($copies as $index => $copy) {
            $value->setPosition($index + 1);
        }
    }

    /**
     * Entry point for teachers to grade student copies
     *
     * @param Request $request
     * @param User $user
     * @param StudentCopy $studentCopy
     * @param EntityManagerInterface $entityManager
     * @param ApiRequestValidator $apiRequestValidator
     * @return JsonResponse
     */
    #[Route('/api/evaluation/studentCopy/{id}/grade', name: 'app_student_copy_grade', methods: ['PUT'])]
    public function gradeStudentCopy(
        Request $request,
        #[CurrentUser] User $user,
        StudentCopy $studentCopy,
        EntityManagerInterface $entityManager,
        ApiRequestValidator $apiRequestValidator,
    ): JsonResponse
    {

        // TODO: Make this safe

        // Makes sure that the User is the evaluation's author
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');
        $this->isAllowedOnRessource($studentCopy->getEvaluation(), $user);

        // Makes sure that the request has valid/required data
        $dto = $apiRequestValidator->checkRequestValidity($request, GradeCopyDTO::class);

        // Sets the fields on the studentCopy entity
        $studentCopy->setCommentary($dto->commentary);
        $studentCopy->setScore($dto->score);

        $evaluation = $studentCopy->getEvaluation();

        // Filters only the graded copies
        $gradedCopies = $evaluation->getStudentCopies()->filter(function(StudentCopy $copy) {
            return $copy->getScore() !== null;
        });

        // Sets the Evaluation's average score
        $evaluation->setAverageScore($this->avegareCopyScore($gradedCopies));
        $this->computeBillboard($gradedCopies);

        // Flushes changes to database
        $entityManager->flush();

        return $this->json($studentCopy, context: ['groups' => ['fetchStudentCopyPreview']]);
    }

    /**
     * Entry point to get the preview of a studentCopy
     * (placement, grade, class average score, formation, quizz)
     * @param int $id
     * @param User $user
     * @param EntityManagerInterface $entityManager
     * @param NormalizerInterface $normalizer
     * @return JsonResponse|Response
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[Route('/api/evaluation/{id}/studentCopy/preview', name: 'app_student_copy_get', methods: ['GET'])]
    public function getStudentCopyPreview(
        int $id,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        NormalizerInterface $normalizer,
    ): JsonResponse|Response
    {
        $studentCopy = $entityManager->getRepository(StudentCopy::class)->findOneBy(['student' => $user->getId(), 'evaluation' => $id]);

        if (null === $studentCopy) {
            return new Response('Entity not found', Response::HTTP_NOT_FOUND);
        }

        // Creates an associative array with all the requested data
        return $this->createsStudentCopyPreview($normalizer, $studentCopy);
    }

    #[Route('/api/evaluation/studentCopy/preview/last', name: 'app_student_last_copy_preview', methods: ['GET'])]
    public function getLastStudentCopyPreview(
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        NormalizerInterface $normalizer,
    )
    {
        $studentCopy = $entityManager->getRepository(StudentCopy::class)->findLastGradedCopy($user);

        if (null !== $studentCopy) {
            // Creates an associative array with all the requested data
            return $this->createsStudentCopyPreview($normalizer, $studentCopy);
        }

        return $this->json([], Response::HTTP_NOT_FOUND);

    }

    /**
     * @param NormalizerInterface $normalizer
     * @param StudentCopy|null $studentCopy
     * @return JsonResponse
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function createsStudentCopyPreview(NormalizerInterface $normalizer, ?StudentCopy $studentCopy): JsonResponse
    {
        $responseData = array('studentCopy' => $normalizer->normalize($studentCopy, context: ['groups' => 'fetchStudentCopyPreview']));

        $evaluation = $studentCopy->getEvaluation();

        $responseData['formation'] = $normalizer->normalize($evaluation->getFormation(), context: ['groups' => 'api']);
        $responseData['evaluation'] = $normalizer->normalize($evaluation, context: ['groups' => 'api']);
        $responseData['evaluation']['maxScore'] = $evaluation->getQuiz()->getQuestions()->reduce(function(int $accumulator, Question $question): int {
            return $accumulator + $question->getMaxScore();
        }, 0);
        $responseData['evaluation']['copyCount'] = $evaluation->getStudentCopies()->count();

        return $this->json($responseData);
    }
}
