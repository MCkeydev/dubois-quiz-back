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
 * Contrôleur pour la gestion des pages d'accueil pour différents types d'utilisateurs.
 */
class HomeController extends AbstractController
{
    /**
     * Récupère toutes les données nécessaires pour le tableau de bord de l'utilisateur.
     *
     * @param User $user L'utilisateur connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant les données du tableau de bord.
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

    /**
     * Affiche les informations nécessaires pour la page d'accueil d'un utilisateur formateur.
     *
     * @param User $user L'utilisateur formateur connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant les évaluations à noter.
     */
    public function teacherHome(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupères toutes les évaluations avec des copies à noter
        $evaluations = $entityManager->getRepository(Evaluation::class)->findEvaluationsToGrade($user);
        return $this->json($evaluations, context: ['groups' => 'api']);
    }

    /**
     * Affiche les informations nécessaires pour la page d'accueil d'un utilisateur élève.
     *
     * @param User $user L'utilisateur élève connecté.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant les informations pour la page d'accueil de l'élève.
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
