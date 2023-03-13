<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StudentCopyController extends AbstractController
{
    #[Route('/student/copy', name: 'app_student_copy')]
    public function index(): Response
    {
        return $this->render('student_copy/index.html.twig', [
            'controller_name' => 'StudentCopyController',
        ]);
    }
}
