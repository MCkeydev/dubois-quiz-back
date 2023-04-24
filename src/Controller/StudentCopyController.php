<?php

namespace App\Controller;

use App\DTO\GradeCopyDTO;
use App\Entity\Evaluation;
use App\Entity\Question;
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
     * Route to create a student copy object.
     * Only allowed on students.
     * @return Response
     * @throws \JsonException
     */
    #[Route('/api/evaluation/{id}/studentCopy', name: 'app_student_copy_create', methods: ['POST'])]
    public function createStudentCopy (
        #[CurrentUser] User $user,
        Request $request,
        Evaluation $evaluation,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): Response
    {
        // TODO: optimize this (no need to run validation twice)

        // Checks if this the user is a student.
        $this->denyAccessUnlessGranted('ROLE_ELEVE');

        /** Checking user is allowed */

        // Fetches all the formations corresponding to the evaluation
        $formation = $evaluation->getFormation();

        // Checks if the user is a part of the formation
        if (!$formation->getUsers()->contains($user)) {
            throw new AccessDeniedException();
        }

        $dateNow =  new \DateTimeImmutable();

        /** Checking the timeframe is not over */
        if ($evaluation->getStartsAt() > $dateNow || $dateNow > $evaluation->getEndsAt()) {
            throw new \JsonException('The evaluation can only be accessed within its time frame');
        }

        /**
         * @var StudentCopy $studentCopy
         */
        $studentCopy = $apiRequestValidator->checkRequestValidity($request, StudentCopy::class);

        $studentCopy->setStudent($user);
        $studentCopy->setProfessor($evaluation->getAuthor());
        $studentCopy->setEvaluation($evaluation);

        $errors = $validator->validate($studentCopy);

        if (count($errors) > 0) {
            throw new JsonException($errors);
        }

        $entityManager->persist($studentCopy);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($studentCopy, 'json', [ 'groups' => 'fetchStudentCopy' ]), Response::HTTP_CREATED);
    }

    #[Route('/api/evaluation/studentCopy/{id}/submit', name: 'app_student_copy_submit', methods: ['GET'])]
    public function submitStudentCopy(
        #[CurrentUser] User $user,
        StudentCopy $studentCopy,
        EntityManagerInterface $entityManager,
    )
    {
        $this->isAllowedOnRessource($studentCopy, $user);

        $studentCopy->setIsLocked(true);
        $entityManager->flush();

        return $this->json($studentCopy, context: ['groups' => 'fetchStudentCopy']);
    }

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
        // Creates an associative array with all the requested data
        return $this->createsStudentCopyPreview($normalizer, $studentCopy);
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
