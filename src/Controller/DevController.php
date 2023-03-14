<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class DevController extends AbstractController
{
    #[Route('/api/dev/user', name: 'app_dev')]
    public function index(UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager): Response
    {
        $formation = new Formation();
        $formation->setName('BTS SIO SLAM 2ème année');

        $user = new User();
        $user->setName('michele')
            ->setEmail('michele.florio@ufa47.org.fr')
            ->setSurname('Florio')
            ->setRoles(["ROLE_ELEVE"]);

        $user->setPassword($hasher->hashPassword($user, 'password'));

        $entityManager->persist($formation);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('dev/index.html.twig', [
            'controller_name' => 'DevController',
        ]);
    }
}
