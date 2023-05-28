<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Contrôleur pour la gestion des quiz.
 */
class QuizController extends AbstractApiController
{
    /**
     * Récupère tous les quiz d'un professeur.
     *
     * @param User $user L'utilisateur formateur connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant les quiz du formateur.
     */
    #[Route('api/quizzes', methods: ['GET'])]
    public function getQuizzes(
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        $quizzes = $entityManager->getRepository(Quiz::class)->findBy(['author' => $user]);

        if (0 === count($quizzes)) {
            $this->createNotFoundException();
        }

        return $this->json($quizzes, context: ['groups' => 'api']);
    }

    /**
     * Crée un nouveau quiz.
     *
     * @param User $user L'utilisateur formateur connecté.
     * @param Request $request L'objet Request contenant les données de la requête.
     * @param ApiRequestValidator $apiRequestValidator Le service de validation des requêtes API.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON indiquant la création réussie du quiz.
     */
    #[Route('/api/quiz', name: 'app_quiz', methods: ['POST'])]
    public function createQuiz(
        #[CurrentUser] User $user,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');
        /**
         * @var Quiz $dto
         */
        $dto = $apiRequestValidator->checkRequestValidity($request, Quiz::class, isArray: false);

        // Calcule la note maximale du quiz en faisant la somme des barêmes de chaque question
        $maxScore = $dto->getQuestions()->reduce(function (int $accumulator, Question $question): int {
            return $accumulator + $question->getMaxScore();
        }, 0);

        $dto->setMaxScore($maxScore);

        $dto->setAuthor($user);
        $entityManager->persist($dto);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz was succesfully created.', 'id' => $dto->getId()], Response::HTTP_CREATED);
    }

    /**
     * Met à jour un quiz existant.
     *
     * @param User $user L'utilisateur connecté.
     * @param Quiz $quiz Le quiz à mettre à jour.
     * @param Request $request L'objet Request contenant les données de la requête.
     * @param SerializerInterface $serializer L'instance du Serializer.
     * @param ApiRequestValidator $apiRequestValidator Le service de validation des requêtes API.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON indiquant la mise à jour réussie du quiz.
     */
    #[Route('/api/quiz/{id}', name: 'app_quiz_update', methods: ['PATCH'])]
    public function updateQuiz(
        #[CurrentUser] User $user,
        Quiz $quiz,
        Request $request,
        SerializerInterface $serializer,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // Vérifie si l'utilisateur est le propriétaire du quiz
        $this->isAllowedOnRessource($quiz, $user);

        $apiRequestValidator->checkRequestValidity($request, Question::class);

        // Si l'utilisateur est autorisé, nous devons valider la requête
        $requestContent = json_decode($request->getContent(), true);

        /*
         * Pour chaque entrée dans le corps de la requête, vérifie si cette propriété existe dans l'objet,
         * et si oui, remplace son contenu.
         */
        $this->deepSetProperties($requestContent, $quiz);

        // Puisque la validation a été effectuée dans checkRequestValidity, nous pouvons persister sans réexécuter la validation
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($quiz, 'json', context: ['groups' => 'api']), Response::HTTP_OK);
    }

    /**
     * Supprime un quiz.
     *
     * @param User $user L'utilisateur connecté.
     * @param Quiz $quiz Le quiz à supprimer.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON indiquant la suppression réussie du quiz.
     */
    #[Route('/api/quiz/{id}', name: 'app_quiz_deletequiz', methods: ['DELETE'])]
    public function deleteQuiz(
        #[CurrentUser] User $user,
        Quiz $quiz,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // Vérifie si l'utilisateur est le propriétaire du quiz
        $this->isAllowedOnRessource($quiz, $user);

        $entityManager->remove($quiz);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}
