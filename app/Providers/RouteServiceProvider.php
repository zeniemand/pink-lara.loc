<?php

namespace Corp\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

use Corp\Portfolio;
use Corp\User;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'Corp\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        //Валидация адресной строки для параметра alias:
        $router->pattern('alias', '[\w-]+');

        parent::boot($router);

        //Зададим пользовательское действие для маршрута /articles параметра articles:
        $router->bind('articles', function ($value){
            //получаем модель Article для переданного значения поля alias:
            return \Corp\Article::where('alias', $value)->first();
        });

        //Пользовательское действие для маршрута /portfolios параметра portfolios:
        $router->bind('portfolios', function ($value){
            //Получаем модель Portfolio по переданному значению поля alias:
            return Portfolio::where('alias', $value)->first();
        });


        //Зададим пользовательское действие для маршрута /menus параметра menus:
        $router->bind('menus', function ($value){
            //Получаем модель Menu:
            return \Corp\Menu::where('id', $value)->first();
        });

        //Привязываем к занчению параметра users, модель пользователя при помощи фукнции обработчика:
        $router->bind('users', function ($value){
            return User::where('id', $value)->first();
        });
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapWebRoutes($router);

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        $router->group([
            'namespace' => $this->namespace, 'middleware' => 'web',
        ], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
