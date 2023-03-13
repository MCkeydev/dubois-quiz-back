<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\User;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class AnswerController extends AbstractApiController
{
    #[Route('/api/question/{id}/answer', name: 'app_answer_answer', methods: ['POST'])]
    public function createAnswer(
                                 #[CurrentUser] $user,
                                 Request $request,
                                 Question $question,
                                 ApiRequestValidator $apiRequestValidator,
                                 EntityManagerInterface $entityManager): Response
    {
        // Checks if the user is allowed to modify this ressource.
        $this->isAllowedOnRessource($question->getQuiz(), $user);

        // Validates the request for type/validation errors.
        $dto = $apiRequestValidator->checkRequestValidity($request, Answer::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        // Persists the entity in the entity manager
        $entityManager->persist($dto);

        // Adds the answer to the question.
        $question->addAnswer($dto);

        // Persist all changes to database.
        $entityManager->flush();

        return $this->json($dto, context: [ 'groups' => 'api' ]);
    }

    #[Route('/api/answer/{id}', name: 'app_answer_update', methods: ['PATCH'])]
    public function updateQuiz(
        #[CurrentUser] User $user,
        Answer $answer,
        Request $request,
        SerializerInterface $serializer,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        // Checks if the user is allowed to modify this ressource.
        $this->isAllowedOnRessource($answer->getQuestion()->getQuiz(), $user);

        // Validates the request for type/validation errors.
        $apiRequestValidator->checkRequestValidity($request, Answer::class);

        // We fetch the content in an associative array form.
        $requestContent = json_decode($request->getContent(), true);

        /**
         * For each entry in the request body, check if such property exist in object,
         * and if it does, replace its content.
         */
        $this->deepSetProperties($requestContent, $answer);

        // Since validation was made in checkRequestValidity, we can persist without revalidating.
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($answer->getQuestion(), 'json', context: [ 'groups' => 'api' ]), Response::HTTP_OK);
    }

    #[Route('/api/answer/{id}', name: 'app_quiz_deleteanswer', methods: ['DELETE'])]
    public function deleteAnswer(
        #[CurrentUser] $user,
        Answer $answer,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        // Checks if the user is allowed to modify this ressource.
        $this->isAllowedOnRessource($answer->getQuestion()->getQuiz(), $user);

        $entityManager->remove($answer);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}
