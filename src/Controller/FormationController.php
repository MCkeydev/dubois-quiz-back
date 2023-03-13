<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Service\ApiRequestValidator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class FormationController extends AbstractApiController
{
    #[Route('/api/formation', name: 'app_formation_create', methods: ['POST'])]
    public function createFormation (Request $request,
                                    ApiRequestValidator $apiRequestValidator,
                                    EntityManagerInterface $entityManager,
                                    SerializerInterface $serializer

    ): Response
    {
        /**
         * @var Formation $formation
         */
        $formation = $apiRequestValidator->checkRequestValidity($request, Formation::class);

        $entityManager->persist($formation);
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($formation, 'json'));
    }

    #[Route('/api/formation/{id}', name: 'app_formation_update', methods: ['PATCH'])]
    public function updateFormation (
        Formation $formation,
        Request $request,
        ApiRequestValidator $apiRequestValidator,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
    ): Response
    {

        // Validates the request for type/validation errors.
        $apiRequestValidator->checkRequestValidity($request, Formation::class);

        // We fetch the content in an associative array form.
        $requestContent = json_decode($request->getContent(), true);

        /**
         * For each entry in the request body, check if such property exist in object,
         * and if it does, replace its content.
         */
        $this->deepSetProperties($requestContent, $formation);

        // Since validation was made in checkRequestValidity, we can persist without revalidating.
        $entityManager->flush();

        return new JsonResponse($serializer->serialize($formation, 'json', context: [ 'groups' => 'api' ]), Response::HTTP_OK);
    }

    #[Route('/api/formation/{id}', name: 'app_formation_create', methods: ['DELETE'])]
    public function deleteFormation (Formation $formation,
                                    EntityManagerInterface $entityManager,
    ) {
        $entityManager->remove($formation);
        $entityManager->flush();

        return $this->json('Entity sucessfully removed');
    }
}
