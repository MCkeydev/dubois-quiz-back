<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationList;

class AnswerController extends AbstractController
{
    #[Route('/api/question/{id}/answer', name: 'app_answer_answer', methods: ['POST'])]
    public function createAnswer(Request $request,
                                 Question $question,
                                 ApiRequestValidator $apiRequestValidator,
                                 EntityManagerInterface $entityManager): Response
    {
        $dto = $apiRequestValidator->checkRequestValidity($request, Answer::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($dto);

        $question->addAnswer($dto);

        $entityManager->flush();

        return $this->json($dto, context: [ 'groups' => 'api' ]);
    }
}
