<?php

namespace App\Controller;

use App\Entity\User;
use App\Interfaces\OwnedEntityInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

abstract class AbstractApiController extends AbstractController
{
    public function isAllowedOnRessource(OwnedEntityInterface $entity,User $user) {
        if (!$entity->isOwner($user)) {
            throw new UnauthorizedHttpException('You are not allowed to access this entity', Response::HTTP_UNAUTHORIZED);
        }
    }
}