<?php

namespace App\Interfaces;

use App\Entity\User;

interface OwnedEntityInterface
{
    public function isOwner(User $user): bool;
}