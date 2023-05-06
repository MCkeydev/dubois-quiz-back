<?php

namespace App\Controller;

use App\DTO\CreateStudentAnswerDTO;
use App\DTO\GradeCopyDTO;
use App\DTO\GradeStudentAnswerDTO;
use App\Entity\Answer;
use App\Entity\Evaluation;
use App\Entity\Question;
use App\Entity\StudentAnswer;
use App\Entity\StudentCopy;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class controlleur de gestion de copies élève (création, correction).
 */
class StudentCopyController extends AbstractApiController
{
    /**
     * Route pour créer la copie, et créer toutes les réponses soumises par l'élève.
     */
    #[
        Route(
            '/api/evaluation/{id}/studentCopy/submit',
            name: 'app_student_copy_submit',
            methods: ['POST']
        )
    ]
    public function submitStudentCopy(
        #[CurrentUser] User $user,
        Request $request,
        Evaluation $evaluation,
        EntityManagerInterface $entityManager,
        ApiRequestValidator $apiRequestValidator,
        ValidatorInterface $validator
    ): JsonResponse|Response {
        // Fetches all the formations allowed on the evaluation
        $formation = $evaluation->getFormation();

        /*
         * Vérifie 3 choses :
         * - Que l'utilisateur est bien dans une formation visée par l'évaluation
         * - Que l'utilisateur n'a pas déjà fait l'évaluation
         * - Et que l'évaluation est en cours
         *
         */
        if (!$formation->getUsers()->contains($user) || null !== $entityManager
                ->getRepository(StudentCopy::class)
                ->findOneBy(['student' => $user, 'evaluation' => $evaluation])
            || (new \DateTimeImmutable() < $evaluation->getStartsAt()
            || new \DateTimeImmutable() > $evaluation->getEndsAt())) {
            throw new AccessDeniedException();
        }

        // Decodes the json to allow iterating the request content
        $requestArray = json_decode($request->getContent(), true);

        // Verifies que le contenu de la requête est bien un tableau
        if (null === $requestArray) {
            throw new \JsonException(new ConstraintViolation('The request must of type array', '', [], null, 'request.format', null));
        }

        // Stores all the evalution's questions
        $questions = $evaluation->getQuiz()->getQuestions();

        $dtoArray = [];

        /*
         * Validates each of the request's array elements to match the DTO
         * Now that we have an array of DTO's, we want to make sure that the question is in fact,
         * related to the evaluation.
         */
        foreach ($requestArray as $_ => $value) {
            // Checks the type and structure of request and instantiates a dto
            $dtoArray[] = $apiRequestValidator->checkRequestValidity(
                json_encode($value),
                CreateStudentAnswerDTO::class,
                isArray: true
            );
        }

        // If no studentCopy was found, we create one
        $studentCopy = (new StudentCopy())
            ->setEvaluation($evaluation)
            ->setStudent($user);

        $entityManager->persist($studentCopy);

        // TODO : comment this, and throw errors
        // Loops over all the dtos to create corresponding student answers
        /**
         * @var CreateStudentAnswerDTO $dto
         */
        foreach ($dtoArray as $_ => $dto) {
            // If the request's question is present in the evaluation's question, we can continue
            if (
                $question = $questions->findFirst(function (
                    int $key,
                    Question $value
                ) use ($dto) {
                    return $value->getId() === $dto->question;
                })
            ) {
                // Gets all the answers of the question
                $answers = $question->getAnswers();

                // If there is more than one, and one corresponds to the choice in the dto
                if (
                    $answers->count() > 1 &&
                    $dto->choice &&
                    ($answer = $answers->findFirst(function (
                        int $key,
                        Answer $value
                    ) use ($dto) {
                        return $value->getId() === $dto->choice;
                    }))
                ) {
                    $studentAnswer = (new StudentAnswer())
                        ->setStudentCopy($studentCopy)
                        ->setQuestion($question)
                        ->setChoice($answer);
                } elseif ($dto->answer) {
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

                    return new Response(
                        $errorsString,
                        Response::HTTP_BAD_REQUEST
                    );
                }

                $entityManager->persist($studentAnswer);
            }
        }

        $studentCopy->setIsLocked(true);
        $entityManager->flush();

        return $this->json(
            $studentCopy,
            context: ['groups' => 'fetchStudentCopy']
        );
    }

    /**
     * Calculates the average score of an evaluation.
     *
     * @param ReadableCollection $gradedCopies // Copies notées
     *
     * @return int // La note moyenne de toutes les copies
     */
    private function avegareCopyScore(ReadableCollection $gradedCopies): int
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
     * Computes the position of each copy, and sets it.
     *
     * @param ReadableCollection $gradedCopies Collection of copies with a non-null score
     */
    private function computeBillboard(ReadableCollection $gradedCopies): void
    {
        // Gets all the copies in array form
        $copies = $gradedCopies->getValues();

        // Sorts in DESC order
        usort($copies, function ($a, $b) {
            return $b->getScore() <=> $a->getScore();
        });

        // Sets the position as the copy index + 1
        /*
         * @var StudentCopy $value
         */
        foreach ($copies as $index => $copy) {
            $copy->setPosition($index + 1);
        }
    }

    /**
     * Route pour que les professeurs puissent corriger une copie d'un élève.
     *
     * @param User $user
     */
    #[
        Route(
            '/api/evaluation/studentCopy/{id}/grade',
            name: 'app_student_copy_grade',
            methods: ['PUT']
        )
    ]
    public function gradeStudentCopy(
        Request $request,
        #[CurrentUser] User $user,
        StudentCopy $studentCopy,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ApiRequestValidator $apiRequestValidator
    ): Response {
        // Assures que l'utilisateur est bien l'auteur de l'évaluation
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');
        $this->isAllowedOnRessource($studentCopy->getEvaluation(), $user);

        // Validation du contenu de la requête
        $dto = $apiRequestValidator->checkRequestValidity(
            $request->getContent(),
            GradeCopyDTO::class
        );

        /*
         * Nous savons que le DTO possède bien un commentaire et un tableau,
         * maintenant il faut vérifier le contenu du tableau
         */
        /**
         * @var GradeCopyDTO $dto
         */
        foreach ($dto->answers as $_ => $value) {
            // Nous validons chaque réponse corrigée envoyée par le correcteur
            $answer = $apiRequestValidator->checkRequestValidity(
                json_encode($value),
                GradeStudentAnswerDTO::class
            );

            // Il faut vérifier si la réponse est bel et bien sur la copie à corriger
            $studentAnswer = $entityManager
                ->getRepository(StudentAnswer::class)
                ->findOneBy([
                    'studentCopy' => $studentCopy,
                    'id' => $answer->answerId,
                ]);

            if (null !== $studentAnswer) {
                $studentAnswer->setAnnotation($answer->annotation);
                $studentAnswer->setScore($answer->score);

                $errors = $validator->validate($studentAnswer);

                if (count($errors) > 0) {
                    /*
                     * Uses a __toString method on the $errors variable which is a
                     * ConstraintViolationList object. This gives us a nice string
                     * for debugging.
                     */
                    $errorsString = (string) $errors;

                    return new Response(
                        $errorsString,
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }
        }

        // Sets the fields on the studentCopy entity
        $studentCopy->setCommentary($dto->commentary);

        $studentCopy->setScore(
            $studentCopy
                ->getStudentAnswers()
                ->reduce(function (int $accumulator, StudentAnswer $value) {
                    if ($value->getScore()) {
                        return $accumulator + $value->getScore();
                    } else {
                        return $accumulator;
                    }
                }, 0)
        );

        $evaluation = $studentCopy->getEvaluation();

        // Filters only the graded copies
        $gradedCopies = $evaluation
            ->getStudentCopies()
            ->filter(function (StudentCopy $copy) {
                return null !== $copy->getScore();
            });

        // Sets the Evaluation's average score
        $evaluation->setAverageScore($this->avegareCopyScore($gradedCopies));
        $this->computeBillboard($gradedCopies);

        // Flushes changes to database
        $entityManager->flush();

        return $this->json(
            $studentCopy,
            context: ['groups' => ['fetchStudentCopyPreview']]
        );
    }

    /**
     * Entry point to get the preview of a studentCopy
     * (placement, grade, class average score, formation, quizz).
     *
     * @param User $user
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    #[
        Route(
            '/api/evaluation/{id}/studentCopy/preview',
            name: 'app_student_copy_get',
            methods: ['GET']
        )
    ]
    public function getStudentCopyPreview(
        int $id,
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        NormalizerInterface $normalizer
    ): JsonResponse|Response {
        $studentCopy = $entityManager
            ->getRepository(StudentCopy::class)
            ->findOneBy(['student' => $user->getId(), 'evaluation' => $id]);

        if (null === $studentCopy) {
            return new Response('Entity not found', Response::HTTP_NOT_FOUND);
        }

        // Creates an associative array with all the requested data
        return $this->createsStudentCopyPreview($normalizer, $studentCopy);
    }

    #[
        Route(
            '/api/evaluation/studentCopy/preview/last',
            name: 'app_student_last_copy_preview',
            methods: ['GET']
        )
    ]
    public function getLastStudentCopyPreview(
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
        NormalizerInterface $normalizer
    ) {
        $studentCopy = $entityManager
            ->getRepository(StudentCopy::class)
            ->findLastGradedCopy($user);

        if (null !== $studentCopy) {
            // Creates an associative array with all the requested data
            return $this->createsStudentCopyPreview($normalizer, $studentCopy);
        }

        return $this->json([], Response::HTTP_NOT_FOUND);
    }

    /**
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    private function createsStudentCopyPreview(
        NormalizerInterface $normalizer,
        ?StudentCopy $studentCopy
    ): JsonResponse {
        $responseData = [
            'studentCopy' => $normalizer->normalize(
                $studentCopy,
                context: ['groups' => 'fetchStudentCopyPreview']
            ),
        ];

        $evaluation = $studentCopy->getEvaluation();

        $responseData['formation'] = $normalizer->normalize(
            $evaluation->getFormation(),
            context: ['groups' => 'api']
        );
        $responseData['evaluation'] = $normalizer->normalize(
            $evaluation,
            context: ['groups' => 'api']
        );
        $responseData['evaluation']['maxScore'] = $evaluation
            ->getQuiz()
            ->getQuestions()
            ->reduce(function (int $accumulator, Question $question): int {
                return $accumulator + $question->getMaxScore();
            }, 0);
        $responseData['evaluation']['copyCount'] = $evaluation
            ->getStudentCopies()
            ->count();

        return $this->json($responseData);
    }
}
