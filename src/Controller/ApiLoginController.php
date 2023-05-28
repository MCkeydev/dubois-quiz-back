<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Contrôleur pour l'API de connexion.
 */
class ApiLoginController extends AbstractController
{
    /**
     * Affiche les informations de connexion de l'utilisateur.
     *
     * Cette méthode gère la route "/api/login" et renvoie les informations de connexion de l'utilisateur.
     *
     * @param User|null $user L'utilisateur actuellement connecté, annoté avec #[CurrentUser].
     * @return JsonResponse La réponse JSON contenant les informations de connexion.
     */
    #[Route('/api/login', name: 'app_api_login', methods: ['POST', 'GET'])]
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ApiLoginController.php',
            'user' => $user->getUserIdentifier(),
            'token' => base64_encode('ezqsdqsdo'),
        ]);
    }
}
