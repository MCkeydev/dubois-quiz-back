<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Classe ApiRequestValidator
 *
 * Cette classe est responsable de la validation des requêtes API.
 */
class ApiRequestValidator
{
    /**
     * @var SerializerInterface Interface de sérialisation/désérialisation des objets
     */
    public SerializerInterface $serializer;

    /**
     * @var ValidatorInterface Interface de validation des objets
     */
    public ValidatorInterface $validator;

    /**
     * Constructeur de la classe ApiRequestValidator
     *
     * @param SerializerInterface $serializer Interface de sérialisation/désérialisation des objets
     * @param ValidatorInterface $validator Interface de validation des objets
     */
    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * Vérifie la validité d'une requête
     *
     * @param Request|string $request La requête à valider
     * @param string $type Le type d'objet dans lequel désérialiser la requête
     * @param string $format Le format de la requête (par défaut : 'json')
     * @param bool $isArray Indique si l'objet désérialisé est un tableau (par défaut : true)
     * @return mixed L'objet désérialisé si la validation réussit
     * @throws JsonException Si des erreurs de validation sont détectées
     */
    public function checkRequestValidity(Request|string $request, string $type, string $format = 'json', $isArray = true): mixed
    {
        try {
            $dto = $this->serializer->deserialize(!$isArray ? $request->getContent() : $request, $type, $format, [
                DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
            ]);

            $errors = $this->validator->validate($dto);

            if (count($errors) > 0) {
                /*
                 * Utilise une méthode __toString sur la variable $errors qui est un objet ConstraintViolationList.
                 * Cela nous donne une belle chaîne de caractères pour le débogage.
                 */
                throw new JsonException($errors);
            }

            return $dto;
        } catch (PartialDenormalizationException $e) {
            $violations = new ConstraintViolationList();

            foreach ($e->getErrors() as $exception) {
                $message = sprintf('Le type doit être l\'un des suivants : "%s" ("%s" donné).', implode(', ', $exception->getExpectedTypes()), $exception->getCurrentType());
                $parameters = [];

                if ($exception->canUseMessageForUser()) {
                    $parameters['hint'] = $exception->getMessage();
                }

                $violations->add(new ConstraintViolation($message, '', $parameters, null, $exception->getPath(), null));
            }

            throw new JsonException($violations);
        }
    }
}

