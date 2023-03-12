<?php

namespace App\Interfaces;

use App\Entity\User;

interface OwnedEntityInterface extends EntityInterface
{
    public function isOwner(User $user): bool;
}