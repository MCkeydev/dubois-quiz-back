<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticationSuccessListener
{
    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $data['data'] = [
            'id' => $user->getId(),
            'email' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
        ];

        $event->setData($data);
    }
}
