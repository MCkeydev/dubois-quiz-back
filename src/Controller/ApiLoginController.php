<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login_check', name: 'app_api_login_check', methods: ['POST', 'GET'])]
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

    #[Route('/api/login', name: 'app_api_login', methods: ['POST', 'GET'])]
    public function index2(#[CurrentUser] ?User $user): JsonResponse
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
