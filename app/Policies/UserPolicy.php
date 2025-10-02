<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Пользователь может просматривать только свои данные.
     *
     * @param User $authUser
     * @param User $user
     * @return bool
     */
    public function view(User $authUser,User $user): bool
    {
        return $authUser->id === $user->id;
    }
}
