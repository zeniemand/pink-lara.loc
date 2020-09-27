<?php

namespace Corp\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

use Corp\Article;
use Corp\Policies\ArticlePolicy;

use Corp\Portfolio;
use Corp\Policies\PortfolioPolicy;

use Corp\Permission;
use Corp\Policies\PermissionPolicy;

use Corp\Menu;
use Corp\Policies\MenusPolicy;

use Corp\User;
use Corp\Policies\UserPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Article::class => ArticlePolicy::class,
        Permission::class => PermissionPolicy::class,
        Menu::class => MenusPolicy::class,
        Portfolio::class => PortfolioPolicy::class,
        User::class => UserPolicy::class
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        //Условие разрешений просмотра админки:
        $gate->define('VIEW_ADMIN', function ($user){
            return $user->canDo('VIEW_ADMIN', false);
        });

        //Условие разрешений просмотра статей в админке:
        $gate->define('VIEW_ADMIN_ARTICLES', function ($user){
            return $user->canDo('VIEW_ADMIN_ARTICLES', false);
        });

        //Условие разрешений просмотра отдела редактирования прав доступа:
        $gate->define('EDIT_USERS', function ($user){
            return $user->canDo('EDIT_USERS', false);
        });

        //Условие разрешения просмотра отдела редактирования меню:
        $gate->define('VIEW_ADMIN_MENU', function($user){
            return $user->canDo('VIEW_ADMIN_MENU', false);
        });

        //Условие разрешения простмотра раздела портфолио в админке:
        $gate->define('VIEW_ADMIN_PORTFOLIOS', function ($user){
            return $user->canDo('VIEW_ADMIN_PORTFOLIOS', false);
        });

        //Условие разрешения просмотра раздела пользователей в админке:
        $gate->define('VIEW_ADMIN_USERS', function ($user){
            return $user->canDo('VIEW_ADMIN_USERS', false);
        });


    }
}
