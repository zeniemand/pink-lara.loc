<?php

namespace Corp\Policies;

use Corp\Repositories\PortfoliosRepository;
use Illuminate\Auth\Access\HandlesAuthorization;

use Corp\User;
use Corp\Portfolio;
use Illuminate\Http\Client\Request;

class PortfolioPolicy
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

    //Проверка разрешений на добавление портфоло:
    public function save(User $user)
    {
        return $user->canDo('ADD_PORTFOLIOS');
    }

    //Проверка разрешений на редактирпование портфолио:
    public function edit(User $user)
    {
        return $user->canDo('EDIT_PORTFOLIOS');
    }

    //Проверка разрешений на удаление портфолио:
    public function destroy(User $user)
    {
        return $user->canDo('DELETE_PORTFOLIOS');
    }
}
