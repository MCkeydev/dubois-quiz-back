<?php

namespace App\Controller;

use App\Entity\Evaluation;
use App\Entity\StudentCopy;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StudentCopyController extends AbstractController
{
    /**
     * Route to create a student copy object.
     * Only allowed on students.
     * @return Response
     */
    #[Route('/api/evaluation/{id}/studentCopy', name: 'app_student_copy', methods: ['POST'])]
    public function index (
        #[CurrentUser] $user,
        Request $request,
        Evaluation $evaluation,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): Response
    {
        // TODO: optimize this (no need to run validation twice)

        // Checks if this the user is a student.
        $this->denyAccessUnlessGranted('ROLE_ELEVE');

        /** Checking user is allowed */
        // Fetches all the formations corresponding to the evaluation
        $formations = $evaluation->getFormations();


        $isAllowed = false;
        // Checks if the user is in one of the Formations of the Evaluation
        foreach ($formations->toArray() as $key => $value) {
            if ($value->getUsers()->contains($user)) {
                $isAllowed = true;
            };

            if (!$isAllowed) {
                throw new AccessDeniedException();
            };
        }

        $dateNow =  new \DateTimeImmutable();

        /** Checking the timeframe is not over */
        if ($evaluation->getStartsAt() > $dateNow || $dateNow > $evaluation->getEndsAt()) {
            throw new \JsonException('The evaluation can only be accessed within its time frame');
        }

        /**
         * @var StudentCopy $studentCopy
         */
        $studentCopy = $apiRequestValidator->checkRequestValidity($request, StudentCopy::class);

        $studentCopy->setStudent($user);
        $studentCopy->setProfessor($evaluation->getAuthor());
        $studentCopy->setEvaluation($evaluation);

        $errors = $validator->validate($studentCopy);

        if (count($errors) > 0) {
            throw new JsonException($errors);
        }

        $entityManager->persist($studentCopy);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($studentCopy, 'json', [ 'groups' => 'fetchStudentCopy' ]), Response::HTTP_CREATED);
    }
}
