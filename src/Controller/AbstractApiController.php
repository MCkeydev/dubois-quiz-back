<?php

namespace App\Controller;

use App\Entity\User;
use App\Interfaces\EntityInterface;
use App\Interfaces\OwnedEntityInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

abstract class AbstractApiController extends AbstractController
{
    private readonly PropertyAccessorInterface $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAcessorInterface) {
        $this->propertyAccessor = $propertyAcessorInterface;
    }

    public function isAllowedOnRessource(OwnedEntityInterface $entity,User $user) {
        if (!$entity->isOwner($user)) {
            throw new UnauthorizedHttpException('You are not allowed to access this entity', Response::HTTP_UNAUTHORIZED);
        }
    }

    public function deepSetProperties (array $properties, EntityInterface $object)
    {
        /**
         * For each entry in the request body, check if such property exist in object,
         * and if it does, replace its content.
         */
        foreach ($properties as $key => $value) {
            if ($this->propertyAccessor->isReadable($object, $key)) {

                $property = $this->propertyAccessor->getValue($object, $key);

                if ($property instanceof Collection) {
                    foreach ($property->toArray() as $item => $itemValue) {
                        $this->deepSetProperties($value[$item], $itemValue);
                    }
                } else {
                    $this->propertyAccessor->setValue($object, $key, $value);
                }
            }
        }
    }
}