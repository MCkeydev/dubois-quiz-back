<?php

namespace App\Controller;

use App\DTO\CreateEvaluationDTO;
use App\DTO\RescheduleEvaluationDTO;
use App\Entity\Evaluation;
use App\Entity\Formation;
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
 * Contrôleur pour permettant de gérer les évaluations.
 */
class EvaluationController extends AbstractApiController
{
    /**
     * Route qui retourne toutes les évaluations créées par l'utilisateur actuellement connecté
     * pour lesquelles aucune copie n'a été soumise.
     *
     * L'utilisateur actuel est automatiquement injecté dans la fonction grâce à l'attribut CurrentUser.
     *
     * Cette méthode est uniquement accessible aux utilisateurs ayant le rôle de 'ROLE_FORMATEUR'.
     *
     * @param User $user L'utilisateur actuellement connecté
     */
    #[Route('/api/evaluations/no-copies', name: 'app_evaluations_no_copies', methods: ['GET'])]
    public function getUserEvaluationsWithoutCopies(#[CurrentUser] User $user, EntityManagerInterface $entityManager): Response
    {
        // Check if the user has the FORMATEUR role
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        $evaluations = $entityManager->getRepository(Evaluation::class)->findUserEvaluationsWithoutCopies($user);

        // Here you can process the evaluations, for instance return them as json.
        return $this->json($evaluations, context: ['groups' => 'api']);
    }

    /**
     * Route qui permet à l'auteur d'une évaluation de reprogrammer cette dernière,
     * à condition qu'aucun élève n'y ai déjà participé.
     *
     * Cette méthode est uniquement accessible aux utilisateurs ayant le rôle de 'ROLE_FORMATEUR'.
     *
     * @param User $user L'utilisateur actuellement connecté
     */
    #[Route('/api/evaluation/{id}', name: 'app_evaluation_reschedule', methods: ['PUT'])]
    public function rescheduleEvaluation(#[CurrentUser] User $user, Evaluation $evaluation, ApiRequestValidator $apiRequestValidator, Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        if (!$evaluation->getStudentCopies()->isEmpty()) {
            return $this->json('Un élève participe déjà à cette évaluation, elle ne peut plus être modifée', Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var RescheduleEvaluationDTO $dto
         */
        $dto = $apiRequestValidator->checkRequestValidity($request, RescheduleEvaluationDTO::class, isArray: false);

        $evaluation->setStartsAt($dto->startsAt)->setEndsAt($dto->endsAt);
        $entityManager->flush();

        return $this->json($evaluation, context: ['groups' => 'api']);
    }

    /**
     * Récupère une évaluation par son ID.
     *
     * @param Evaluation $evaluation L'évaluation à récupérer
     * @param User       $user       L'utilisateur actuel
     *
     * @return JsonResponse La réponse JSON contenant l'évaluation
     */
    #[Route('/api/evaluation/{id}', name: 'app_evaluation_get', methods: ['GET'])]
    public function getEvaluation(
        Evaluation $evaluation,
        #[CurrentUser] User $user,
    ) {
        // Checks if the user is a member of the evaluation's formation
        if (!$user->getFormations()->contains($evaluation->getFormation()) || (new \DateTimeImmutable() < $evaluation->getStartsAt()
                || new \DateTimeImmutable() > $evaluation->getEndsAt())) {
            throw $this->createAccessDeniedException();
        }

        return $this->json($evaluation, context: ['groups' => 'getEvaluation']);
    }

    /**
     * Crée une nouvelle évaluation.
     *
     * @param User                   $user                L'utilisateur actuel
     * @param Request                $request             La requête HTTP
     * @param ApiRequestValidator    $apiRequestValidator Le validateur de requête
     * @param EntityManagerInterface $entityManager       Le gestionnaire d'entités
     * @param SerializerInterface    $serializer          Le sérialiseur
     *
     * @return JsonResponse La réponse JSON contenant l'évaluation crée
     */
    #[Route('/api/evaluation/create', name: 'app_evaluation_create', methods: ['POST'])]
    public function createEvaluation(
        #[CurrentUser] User $user,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse {
        // TODO: Make this in access control
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        // Validates the request format
        /**
         * @var CreateEvaluationDTO $dto
         */
        $dto = $apiRequestValidator->checkRequestValidity($request, CreateEvaluationDTO::class, isArray: false);

        // Recupères le quiz, et vérifie s'il existe et si l'utilisateur est autorisé dessus
        $quiz = $entityManager->getRepository(Quiz::class)->find($dto->quiz);
        if (!$quiz || !$quiz->isOwner($user)) {
            return new JsonResponse("Le quiz renseigné n'est pas valide", Response::HTTP_NOT_FOUND);
        }

        $formation = $entityManager->getRepository(Formation::class)->find($dto->formation);
        $isAllowedOnFormation = $user->getFormations()->contains($formation);

        if (!$isAllowedOnFormation) {
            return new JsonResponse("La formation renseignée n'est pas valide", Response::HTTP_NOT_FOUND);
        }

        $evaluation = new Evaluation();
        $evaluation
            ->setStartsAt($dto->startsAt)
            ->setEndsAt($dto->endsAt)
            ->setAuthor($user)
            ->setQuiz($quiz)
            ->setFormation($formation);

        $entityManager->persist($evaluation);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($evaluation, 'json', ['groups' => 'getEvaluation']), Response::HTTP_CREATED);
    }

    /**
     * Supprime une évaluation.
     *
     * @param User                   $user          L'utilisateur actuel
     * @param Evaluation             $evaluation    L'évaluation à supprimer
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités
     *
     * @return JsonResponse La réponse JSON
     */
    #[Route('/api/evaluation/{id}', name: 'app_evaluation_delete', methods: ['DELETE'])]
    public function deleteEvaluation(
        #[CurrentUser] User $user,
        Evaluation $evaluation,
        EntityManagerInterface $entityManager,
    ) {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($evaluation, $user);

        $entityManager->remove($evaluation);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}
