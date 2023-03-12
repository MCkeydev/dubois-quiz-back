<?php

namespace App\Controller;

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

class QuestionController extends AbstractApiController
{
    #[Route('/api/question/{id}', name: 'app_question_update', methods: ['PUT'])]
    public function modifyQuestion(
        #[CurrentUser] ?User $user,
        Question $question,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        // Checks if the user is the owner of the quizz
        $this->isAllowedOnRessource($question, $user);

        // If the user is allowed, we need to validate the request
        $apiRequestValidator->checkRequest($request, Question::class);

        dd($serializer->deserialize($request, type: "", format: 'json'));

        return new JsonResponse($dto, Response::HTTP_OK);
    }
}
