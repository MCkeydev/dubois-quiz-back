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
 * Contrôleur pour la gestion des copies d'élèves (création, correction).
 */
class StudentCopyController extends AbstractApiController
{
    /**
     * Permet à un professeur de récupérer une copie d'une évaluation dont il est l'auteur.
     *
     * @param User $user L'utilisateur formateur connecté.
     * @param StudentCopy $copy La copie de l'élève.
     * @return JsonResponse La réponse JSON contenant la copie de l'élève.
     */
    #[Route('/api/studentCopy/{id}', methods: 'GET')]
    public function getSingleCopy(#[CurrentUser] User $user, StudentCopy $copy): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');
        $this->isAllowedOnRessource($copy, $user);

        return $this->json($copy, context: ['groups' => 'api']);
    }

    /**
     * Récupère les copies non notées d'une évaluation.
     *
     * @param User $user L'utilisateur formateur connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @param Evaluation $evaluation L'évaluation.
     * @return JsonResponse La réponse JSON contenant les copies non notées.
     */
    #[Route('/api/evaluation/{id}/copies', methods: 'GET')]
    public function getEvaluationUngradedCopies(#[CurrentUser] $user, EntityManagerInterface $entityManager, Evaluation $evaluation)
    {
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        $this->isAllowedOnRessource($evaluation, $user);

        $copies = $entityManager->getRepository(StudentCopy::class)->findStudentCopiesToGrade($evaluation);

        if (0 === count($copies)) {
            $this->createNotFoundException();
        }

        return $this->json($copies, context: ['groups' => 'api']);
    }

    /**
     * Récupère toutes les copies notées et corrigées d'un élève.
     *
     * @param User $user L'utilisateur élève connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant les copies notées et corrigées de l'élève.
     */
    #[Route('/api/studentCopies/graded', name: 'app_studentcopies_graded_get', methods: ['GET'])]
    public function getUsersGradedStudentCopies(
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // Il n'est pas nécessaire d'exécuter ce code si l'utilisateur n'est pas un élève
        $this->denyAccessUnlessGranted('ROLE_ELEVE');

        $studentCopies = $entityManager->getRepository(StudentCopy::class)->findGradedStudentCopies($user);

        if (null === $studentCopies) {
            return $this->json([]);
        } else {
            return $this->json($studentCopies, context: ['groups' => 'studentCopy']);
        }
    }

    /**
     * Récupère une copie notée et corrigée détaillée d'un élève.
     *
     * @param User $user L'utilisateur connecté.
     * @param int $copyID L'ID de la copie.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant la copie notée et corrigée détaillée de l'élève.
     */
    #[Route('/api/studentCopy/{copyID}/graded', name: 'app_studentcopy_getdetailedgradedcopy', methods: ['GET'])]
    public function getDetailedGradedCopy(#[CurrentUser] User $user, int $copyID, EntityManagerInterface $entityManager): JsonResponse
    {
        $copy = $entityManager->getRepository(StudentCopy::class)->findSingleGradedCopy($copyID);
        if (null === $copy) {
            $this->createNotFoundException();
        }
        if (!$this->isAllowedOnRessource($copy, $user)) {
            $this->createAccessDeniedException();
        }

        if (null === $copy) {
            $this->json([], Response::HTTP_NOT_FOUND);
        }

        return $this->json($copy, context: ['groups' => 'studentCopy']);
    }

    /**
     * Soumet une copie d'élève pour une évaluation.
     *
     * @param User $user L'utilisateur élève connecté.
     * @param Request $request L'objet Request contenant les données de la requête.
     * @param Evaluation $evaluation L'évaluation.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @param ApiRequestValidator $apiRequestValidator Le service de validation des requêtes API.
     * @param ValidatorInterface $validator L'instance du Validator.
     * @return JsonResponse|Response La réponse JSON indiquant la soumission réussie de la copie.
     */
    #[Route('/api/evaluation/{id}/studentCopy/submit', name: 'app_student_copy_submit', methods: ['POST'])]
    public function submitStudentCopy(
        #[CurrentUser] User $user,
        Request $request,
        Evaluation $evaluation,
        EntityManagerInterface $entityManager,
        ApiRequestValidator $apiRequestValidator,
        ValidatorInterface $validator
    ): JsonResponse|Response {
        // Récupère toutes les formations autorisées pour l'évaluation
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
            || (new \DateTimeImmutable() <= $evaluation->getStartsAt()
                || new \DateTimeImmutable() >= $evaluation->getEndsAt())) {
            throw new AccessDeniedException();
        }

        // Décode le JSON pour pouvoir itérer sur le contenu de la requête
        $requestArray = json_decode($request->getContent(), true);

        // Vérifie que le contenu de la requête est bien un tableau
        if (null === $requestArray) {
            throw new \JsonException(new ConstraintViolation('The request must of type array', '', [], null, 'request.format', null));
        }

        // Stocke toutes les questions de l'évaluation
        $questions = $evaluation->getQuiz()->getQuestions();

        $dtoArray = [];

        /*
         * Valide chaque élément du tableau de la requête pour correspondre au DTO
         * Maintenant que nous avons un tableau de DTO, nous voulons nous assurer que la question est en fait
         * liée à l'évaluation.
         */
        foreach ($requestArray as $_ => $value) {
            // Vérifie le type et la structure de la requête et instancie un DTO
            $dtoArray[] = $apiRequestValidator->checkRequestValidity(
                json_encode($value),
                CreateStudentAnswerDTO::class,
                isArray: true
            );
        }

        // Si aucune StudentCopy n'a été trouvée, nous en créons une
        $studentCopy = (new StudentCopy())
            ->setEvaluation($evaluation)
            ->setStudent($user);

        $entityManager->persist($studentCopy);

        $studentAnswer = null;

        // TODO : commenter ceci et lancer des erreurs
        // Parcourt tous les DTO pour créer les réponses d'élève correspondantes
        /**
         * @var CreateStudentAnswerDTO $dto
         */
        foreach ($dtoArray as $_ => $dto) {
            // Si la question de la requête est présente dans les questions de l'évaluation, nous pouvons continuer
            if (
                $question = $questions->findFirst(function (
                    int $key,
                    Question $value
                ) use ($dto) {
                    return $value->getId() === $dto->question;
                })
            ) {
                // Récupère toutes les réponses de la question
                $answers = $question->getAnswers();

                // S'il y en a plus d'une et qu'une correspond au choix dans le DTO
                if (
                    $answers->count() >= 1 &&
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
                     * Utilise une méthode __toString() sur la variable $errors qui est un
                     * objet ConstraintViolationList. Cela nous donne une belle chaîne
                     * pour le débogage.
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

        $studentCopy->getEvaluation()->incrementCopyCount();
        $studentCopy->setIsLocked(true);
        $entityManager->flush();

        return $this->json(
            $studentCopy,
            context: ['groups' => 'studentCopy']
        );
    }

    /**
     * Corrige une copie d'élève.
     *
     * @param Request $request L'objet Request contenant les données de la requête.
     * @param User $user L'utilisateur connecté.
     * @param StudentCopy $studentCopy La copie de l'élève à corriger.
     * @param ValidatorInterface $validator L'instance du Validator.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @param ApiRequestValidator $apiRequestValidator Le service de validation des requêtes API.
     * @return Response La réponse indiquant que la copie a été corrigée avec succès.
     */
    #[Route('/api/evaluation/studentCopy/{id}/grade', name: 'app_student_copy_grade', methods: ['PUT'])]
    public function gradeStudentCopy(
        Request $request,
        #[CurrentUser] User $user,
        StudentCopy $studentCopy,
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        ApiRequestValidator $apiRequestValidator
    ): Response {
        // Assure que l'utilisateur est bien l'auteur de l'évaluation
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
                     * Utilise une méthode __toString() sur la variable $errors qui est un
                     * objet ConstraintViolationList. Cela nous donne une belle chaîne
                     * pour le débogage.
                     */
                    $errorsString = (string) $errors;

                    return new Response(
                        $errorsString,
                        Response::HTTP_BAD_REQUEST
                    );
                }
            }
        }

        // Définit les champs sur l'entité StudentCopy
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

        // Filtre uniquement les copies notées
        $gradedCopies = $evaluation
            ->getStudentCopies()
            ->filter(function (StudentCopy $copy) {
                return null !== $copy->getScore();
            });

        // Définit la note moyenne de l'évaluation
        $evaluation->setAverageScore($this->avegareCopyScore($gradedCopies));
        $this->computeBillboard($gradedCopies);

        // Enregistre les modifications dans la base de données
        $entityManager->flush();

        return $this->json(
            $studentCopy,
            context: ['groups' => ['fetchStudentCopyPreview']]
        );
    }

    /**
     * Récupère un aperçu de la dernière copie corrigée d'un élève.
     *
     * @param User $user L'utilisateur connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @param NormalizerInterface $normalizer L'instance du normalizer.
     * @return JsonResponse La réponse JSON contenant l'aperçu de la dernière copie corrigée de l'élève.
     */
    #[
        Route(
            '/api/studentCopy/preview/last',
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
            // Crée un tableau associatif avec toutes les données demandées
            return $this->createsStudentCopyPreview($normalizer, $studentCopy);
        }

        return $this->json([], Response::HTTP_NOT_FOUND);
    }

    /**
     * Crée l'aperçu d'une copie d'élève.
     *
     * @param NormalizerInterface $normalizer L'instance du normalizer.
     * @param StudentCopy|null $studentCopy La copie d'élève.
     * @return JsonResponse La réponse JSON contenant l'aperçu de la copie d'élève.
     */
    private function createsStudentCopyPreview(
        NormalizerInterface $normalizer,
        ?StudentCopy $studentCopy
    ): JsonResponse {
        $responseData = [
            'studentCopy' => $normalizer->normalize(
                $studentCopy,
                context: ['groups' => 'studentCopy']
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

        return $this->json($responseData);
    }

    /**
     * Calcule la note moyenne d'une évaluation.
     *
     * @param ReadableCollection $gradedCopies Les copies notées.
     * @return int La note moyenne de toutes les copies.
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
     * Calcule la position de chaque copie et l'attribue.
     *
     * @param ReadableCollection $gradedCopies La collection de copies avec une note non nulle.
     */
    private function computeBillboard(ReadableCollection $gradedCopies): void
    {
        // Récupère toutes les copies sous forme de tableau
        $copies = $gradedCopies->getValues();

        // Trie par ordre décroissant
        usort($copies, function ($a, $b) {
            return $b->getScore() <=> $a->getScore();
        });

        // Attribue la position en tant qu'index de la copie + 1
        /*
         * @var StudentCopy $value
         */
        foreach ($copies as $index => $copy) {
            $copy->setPosition($index + 1);
        }
    }
}
