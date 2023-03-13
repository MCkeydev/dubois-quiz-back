<?php

namespace App\Controller;

use App\DTO\CreateEvaluationDTO;
use App\Entity\Evaluation;
use App\Entity\Quiz;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class EvaluationController extends AbstractController
{
    #[Route('/api/quiz/{id}/evaluation', name: 'app_evaluation_create', methods: ['POST'])]
    public function createEvaluation(
        #[CurrentUser] $user,
        Request $request,
        Quiz $quiz,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): Response
    {
        // TODO: Make this in access control
        $this->denyAccessUnlessGranted('ROLE_FORMATEUR');

        /**
         * @var $entity Evaluation
         */
        $entity = $apiRequestValidator->checkRequestValidity($request, Evaluation::class);

        $entity->setAuthor($user);
        $entity->setQuiz($quiz);

        $entityManager->persist($entity);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz was succesfully created.', 'id' => $entity->getId()], Response::HTTP_CREATED);

    }
}
