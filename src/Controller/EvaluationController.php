<?php

namespace App\Controller;

use App\DTO\CreateEvaluationDTO;
use App\Entity\Evaluation;
use App\Entity\Quiz;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

class EvaluationController extends AbstractApiController
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
         * @var $evaluation Evaluation
         */
        $evaluation = $apiRequestValidator->checkRequestValidity($request, Evaluation::class);

        $evaluation->setAuthor($user);
        $evaluation->setQuiz($quiz);

        $entityManager->persist($evaluation);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($evaluation, 'json', ['groups' => 'getEvaluation']), Response::HTTP_CREATED);
    }

    #[Route('/api/evaluation/{id}', name: 'app_evaluation_update', methods: ['PATCH'])]
    public function updateEvaluation(
        #[CurrentUser] ?User $user,
        Evaluation $evaluation,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($evaluation, $user);

        $apiRequestValidator->checkRequestValidity($request, CreateEvaluationDTO::class);

        // If the user is allowed, we need to validate the request
        $requestContent = json_decode($request->getContent(), true);

        /**
         * For each entry in the request body, check if such property exist in object,
         * and if it does, replace its content.
         */
        $evaluation = $this->deepSetProperties($requestContent, $evaluation);
        $entityManager->persist($evaluation);

        // Since validation was made in checkRequestValidity, we can persist without revalidating
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($evaluation, 'json', context: [ 'groups' => 'getEvaluation' ]), Response::HTTP_OK);
    }

    #[Route('/api/evaluation/{id}', name: 'app_evaluation_delete', methods: ['DELETE'])]
    public function deleteEvaluation (
        #[CurrentUser] $user,
        Evaluation $evaluation,
        EntityManagerInterface $entityManager,
    )
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($evaluation, $user);

        $entityManager->remove($evaluation);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}
