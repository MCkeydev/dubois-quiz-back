<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class UserController extends AbstractController
{
    #[Route('/api/user', name: 'app_user_get', methods: ['GET'])]
    public function index(#[CurrentUser] User $user): Response
    {
        if (!$user) {
            throw new AccessDeniedException('No user found.');
        }

        return $this->json($user, 200, context: ['groups' => 'getUser']);
    }
}
