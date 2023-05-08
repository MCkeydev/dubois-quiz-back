<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Controller managing homepages for all kinds of users.
 */
class HomeController extends AbstractController
{
    /**
     * Fetches all necessary data for the user's dashboards.
     *
     * @param User $user // Utilisateur connecté
     */
    #[Route('/api/home', name: 'app_home')]
    public function home(
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        // Les données retournées varient en fonction du type d'utilisateur
        if ($this->isGranted('ROLE_ELEVE')) {
            return $this->studentHome($user, $entityManager);
        } elseif ($this->isGranted('ROLE_FORMATEUR')) {
            return $this->teacherHome($user, $entityManager);
        }

        return $this->json($this->createAccessDeniedException());
    }

    public function teacherHome(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupères toutes les évaluations avec des copies à noter
        $evaluations = $entityManager->getRepository(Evaluation::class)->findEvaluationsToGrade($user);
        return $this->json($evaluations, 200, [], ['groups' => ['api']]);
    }

    /**
     * Fonction retournant toutes les informations nécessaire sur la page d'accueil d'un utilisateur élève.
     */
    public function studentHome(
        User $user,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $responseData = [];

        // Formations de l'utilisateur
        $responseData['formations'] = $user->getFormations();

        $evaluationRepository = $entityManager->getRepository(Evaluation::class);

        // Evaluations en cours et à venir de l'utilisateur
        $responseData['onGoing'] = $evaluationRepository->findOngoingEvaluations($user);
        $responseData['incoming'] = $evaluationRepository->findIncomingEvaluations($user);

        return $this->json($responseData, context: ['groups' => 'api']);
    }
}
