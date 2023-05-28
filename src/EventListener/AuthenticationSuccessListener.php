<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Écouteur d'événement pour la réussite de l'authentification.
 */
class AuthenticationSuccessListener
{
    /**
     * Méthode appelée lorsqu'une réponse d'authentification réussit.
     *
     * @param AuthenticationSuccessEvent $event L'événement de réussite d'authentification
     */
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        // Récupérer les données de l'événement
        $data = $event->getData();
        $user = $event->getUser();

        // Vérifier si l'utilisateur est une instance de UserInterface
        if (!$user instanceof UserInterface) {
            return;
        }

        // Ajouter les données supplémentaires à la réponse
        $data['data'] = [
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
        ];

        // Mettre à jour les données de l'événement avec les données modifiées
        $event->setData($data);
    }
}
