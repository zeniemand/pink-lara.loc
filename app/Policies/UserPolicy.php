<?php

namespace Corp\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;

use Corp\User;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function save(User $user)
    {
        return $user->canDo('ADD_USERS');
    }

    public function edit(User $user)
    {
        return $user->canDo('EDIT_USERS');
    }

    public function destroy(User $user)
    {
        return $user->canDo('DELETE_USERS');
    }
}
