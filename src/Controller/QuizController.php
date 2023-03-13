<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Quiz;
use App\Entity\User;
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

class QuizController extends AbstractApiController
{
    #[Route('/api/quiz', name: 'app_quiz', methods: ['POST'])]
    public function createQuiz(
                                #[CurrentUser] User $user,
                               Request $request,
                               ApiRequestValidator $apiRequestValidator,
                                EntityManagerInterface $entityManager,
                              ): JsonResponse
    {
        $dto = $apiRequestValidator->checkRequestValidity($request, Quiz::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        $dto->setAuthor($user);
        $entityManager->persist($dto);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz was succesfully created.', 'id' => $dto->getId()], Response::HTTP_CREATED);
    }

    #[Route('/api/quiz/{id}', name: 'app_quiz_update', methods: ['PATCH'])]
    public function updateQuiz(
        #[CurrentUser] User $user,
        Quiz $quiz,
        Request $request,
        SerializerInterface $serializer,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($quiz, $user);

        $apiRequestValidator->checkRequestValidity($request, Question::class);

        // If the user is allowed, we need to validate the request
        $requestContent = json_decode($request->getContent(), true);

        /**
         * For each entry in the request body, check if such property exist in object,
         * and if it does, replace its content.
         */
        $this->deepSetProperties($requestContent, $quiz);

        // Since validation was made in checkRequestValidity, we can persist without revalidating
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($quiz, 'json', context: [ 'groups' => 'api' ]), Response::HTTP_OK);
    }

    #[Route('/api/quiz/{id}', name: 'app_quiz_deletequiz', methods: ['DELETE'])]
    public function deleteQuiz(
        #[CurrentUser] User $user,
        Quiz $quiz,
        EntityManagerInterface $entityManager,
    ): JsonResponse
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($quiz, $user);

        $entityManager->remove($quiz);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }

}
