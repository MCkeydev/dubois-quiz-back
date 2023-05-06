<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * @param User $user
     */
    #[Route('/api/home', name: 'app_home')]
    public function home(
        #[CurrentUser] User $user,
    ): Response {
        $formations = $user->getFormations();

        return $this->json($formations, context: ['groups' => 'api']);
    }

    /***
     * Route qui retourne toutes les évaluations en cours ou à venir de l'utilisateur.
     *
     * @param User $user // L'utilisateur connecté
     * @param EntityManagerInterface $em
     * @return JsonResponse
     */
    #[Route('/api/evaluations/incoming', name: 'app_evaluations_get', methods: ['GET'])]
    public function getIncomingEvaluations(
        #[CurrentUser] User $user,
        EntityManagerInterface $em,
    ): JsonResponse {
        // Récupère toutes les formations de l'utilisateur
        $formations = $user->getFormations();

        // Initialisation d'un tableau vide pour stocker les traitements de la boucle
        $evaluations = [];

        foreach ($formations as $_ => $formation) {
            $evaluations = array_merge($evaluations, $em->getRepository(Evaluation::class)->findOngoingEvaluations($formation, $user));
        }

        // Si aucune évaluation n'a été trouvée, renvoies une erreur 404
        if (0 === count($evaluations)) {
            return $this->json([], Response::HTTP_NOT_FOUND);
        }

        return $this->json($evaluations, context: ['groups' => 'api']);
    }

    /**
     * Fonction vérifiant si tous les éléments d'un tableau sont eux-mêmes des tableaux vides.
     *
     * @return bool // True si le tableau est rempli de tableaux vides
     */
    public function isDeepEmptyArray(array $tableau): bool
    {
        foreach ($tableau as $element) {
            if (!is_array($element) || 0 !== count($element)) {
                return false;
            }
        }

        return true;
    }
}
