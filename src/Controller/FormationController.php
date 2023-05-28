<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Contrôleur pour la gestion des formations.
 */
class FormationController extends AbstractApiController
{
    /**
     * Récupère les formations associées à un utilisateur.
     *
     * Cette méthode gère la route "/api/formations" et renvoie les formations associées à l'utilisateur connecté.
     *
     * @param User $user L'utilisateur actuellement connecté, annoté avec #[CurrentUser].
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON contenant les formations de l'utilisateur.
     */
    #[Route('api/formations', methods: ['GET'])]
    public function getQuizzes(
        #[CurrentUser] User $user,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        $formations = $user->getFormations();

        if (0 === count($formations)) {
            $this->createNotFoundException();
        }

        return $this->json($formations, context: ['groups' => 'api']);
    }

    /**
     * Supprime une formation.
     *
     * Cette méthode gère la route "/api/formation/{id}" avec la méthode DELETE et supprime la formation spécifiée.
     *
     * @param Formation $formation La formation à supprimer.
     * @param EntityManagerInterface $entityManager L'instance de l'EntityManager.
     * @return JsonResponse La réponse JSON indiquant le succès de la suppression.
     */
    #[Route('/api/formation/{id}', name: 'app_formation_delete', methods: ['DELETE'])]
    public function deleteFormation(Formation $formation,
                                    EntityManagerInterface $entityManager,
    ) {
        $entityManager->remove($formation);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}