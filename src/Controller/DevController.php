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
        $formation->setName('test')
            ->setCreatedAt(new \DateTimeImmutable());

        $user = new User();
        $user->setName('dev')
            ->setEmail('dev2@dev.fr')
            ->setSurname('dev')
            ->setFormation($formation);

        $user->setPassword($hasher->hashPassword($user, 'password'));

        $entityManager->persist($formation);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render('dev/index.html.twig', [
            'controller_name' => 'DevController',
        ]);
    }
}
