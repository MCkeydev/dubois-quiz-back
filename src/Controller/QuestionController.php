<?php

namespace App\Controller;

use App\Entity\Question;
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
use Symfony\Component\Validator\ConstraintViolationList;

class QuestionController extends AbstractApiController
{

    /**
     * This route is meant to create a question entity, and its answer.
     *
     * @param Quiz $quiz
     * @param User $user
     * @param Request $request
     * @param ApiRequestValidator $apiRequestValidator
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    #[Route('/api/quiz/{id}/question', name: 'app_quiz_createquestion', methods: ['POST'])]
    public function createQuestion(
        Quiz $quiz,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
    )
    {
        // TODO: Make this route owner safe

        $dto = $apiRequestValidator->checkRequestValidity($request, Question::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($dto);

        $quiz->addQuestion($dto);
        $entityManager->flush();

        return $this->json($dto, context: [ 'groups' => 'api' ]);
    }


    /**
     * This route is meant to modify a function.
     * @param User|null $user
     * @param Question $question
     * @param Request $request
     * @param ApiRequestValidator $apiRequestValidator
     * @param EntityManagerInterface $entityManager
     * @param SerializerInterface $serializer
     * @return JsonResponse
     */
    #[Route('/api/question/{id}', name: 'app_question_update', methods: ['PATCH'])]
    public function modifyQuestion(
        #[CurrentUser] ?User $user,
        Question $question,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($question, $user);

        $apiRequestValidator->checkRequestValidity($request, Question::class);

        // If the user is allowed, we need to validate the request
        $requestContent = json_decode($request->getContent(), true);

        /**
         * For each entry in the request body, check if such property exist in object,
         * and if it does, replace its content.
         */
        $this->deepSetProperties($requestContent, $question);

        // Since validation was made in checkRequestValidity, we can persist without revalidating
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($question, 'json', context: [ 'groups' => 'api' ]), Response::HTTP_OK);
    }

    #[Route('/api/question/{id}', name: 'app_question_deletequestion', methods: ['DELETE'])]
    public function deleteQuestion(#[CurrentUser] ?User $user, Question $question, EntityManagerInterface $entityManager)
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($question, $user);

        $entityManager->remove($question);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}
