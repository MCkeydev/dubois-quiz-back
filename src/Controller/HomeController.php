<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

/**
 * Controller managing homepages for all kinds of users
 */
class HomeController extends AbstractController
{
    /**
     * Fetches all necessary data for the user's dashboards
     * @param User $user
     * @return Response
     */
    #[Route('/api/home', name: 'app_home')]
    public function home(
        #[CurrentUser] User $user,
    ): Response
    {
        $formations = $user->getFormations();

        return $this->json($formations, context: ['groups' => 'api']);
    }

    #[Route('/api/evaluations/incoming/{id}', name: 'app_evaluation_get', methods: ['GET'])]
    public function getIncomingEvaluations(
        #[CurrentUser] User $user,
        EntityManagerInterface $em,
    ) {
        $formations = $user->getFormations();

        $evaluations = [];


        foreach ($formations as $index => $formation) {
            $evaluations[] = $em->getRepository(Evaluation::class)->findIncomingEvaluations($formation, $user);
        }

        return $this->json($evaluations, context: ['groups' => 'api']);
    }
}