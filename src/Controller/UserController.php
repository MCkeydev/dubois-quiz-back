<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Contrôleur pour gérer les opérations liées aux utilisateurs.
 */
class UserController extends AbstractController
{
    /**
     * Récupère les détails de l'utilisateur authentifié.
     *
     * @param User $user L'utilisateur authentifié
     * @return Response La réponse JSON contenant les détails de l'utilisateur
     * @throws AccessDeniedException si aucun utilisateur n'est trouvé
     */
    #[Route('/api/user', name: 'app_user_get', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        if (!$user) {
            throw new AccessDeniedException('Aucun utilisateur trouvé.');
        }

        return $this->json($user, 200, context: ['groups' => 'getUser']);
    }
}
