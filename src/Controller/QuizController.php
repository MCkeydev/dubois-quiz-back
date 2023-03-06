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
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class QuizController extends AbstractController
{
    #[Route('/api/quiz', name: 'app_quiz', methods: ['POST'])]
    public function createQuiz(
                                #[CurrentUser] User $user,
                               Request $request,
                               ApiRequestValidator $apiRequestValidator,
                                EntityManagerInterface $entityManager,
                              ): JsonResponse
    {
        $dto = $apiRequestValidator->checkRequest($request, Quiz::class);

        if ($dto instanceof ConstraintViolationList) {
            return $this->json($dto, Response::HTTP_BAD_REQUEST);
        }

        $dto->setAuthor($user);
        $entityManager->persist($dto);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Quiz was succesfully created.', 'id' => $dto->getId()], Response::HTTP_CREATED);
    }

    #[Route('/api/quiz/{id}/question', name: 'app_quiz_createquestion', methods: ['POST'])]
    public function createQuestion(
                                    Quiz $quiz,
                                    #[CurrentUser] User $user,
                                    Request $request,
                                    ApiRequestValidator $apiRequestValidator,
                                    EntityManagerInterface $entityManager,
    )
    {
        $dto = $apiRequestValidator->checkRequest($request, Question::class);
        $entityManager->persist($dto);

        $quiz->addQuestion($dto);
        $entityManager->flush();

        return $this->json($dto, context: [ 'groups' => 'api']);
    }
}
