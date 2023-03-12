<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\User;
use App\Interfaces\EntityInterface;
use App\Serializer\Normalizer\PatchDenormalizer;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class QuestionController extends AbstractApiController
{
    #[Route('/api/question/{id}', name: 'app_question_update', methods: ['PATCH'])]
    public function modifyQuestion(
        #[CurrentUser] ?User $user,
        Question $question,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        PropertyAccessorInterface $propertyAccessor,
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
        foreach ($requestContent as $key => $value) {
            if ($propertyAccessor->isReadable($question, $key)) {
                $propertyAccessor->setValue($question, $key, $value);
            }
        }

        // Since validation was made in checkRequestValidity, we can persist without revalidating
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($question, 'json', context: [ 'groups' => 'api' ]), Response::HTTP_OK);
    }
}
