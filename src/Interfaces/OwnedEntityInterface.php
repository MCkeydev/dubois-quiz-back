<?php

namespace App\Interfaces;

use App\Entity\User;

/**
 * Interface pour les entités avec propriétaires.
 */
interface OwnedEntityInterface extends EntityInterface
{
    /**
     * Méthode de l'interface permettant de vérifier si l'utilisateur est le propriétaire de l'entité.
     *
     * @param User $user L'utilisateur à vérifier.
     * @return bool Retourne true si l'utilisateur est le propriétaire de l'entité, sinon false.
     */
    public function isOwner(User $user): bool;
}
