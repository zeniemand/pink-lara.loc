<?php


//Контроллер типа REST для главной страницы, использовать будетм только методы index, и для пути переименуем его в home:
Route::resource('/','IndexController', [
                                                    'only' => ['index'],
                                                    'names' => [
                                                        'index' => 'home'
                                                    ]
]);

//Маршрут для обработки страниц связанных с portfolio
Route::resource('portfolios', 'PortfolioController', [
                                                        'parameters' => [
                                                            'portfolios' => 'alias'

                                                        ]
]);

//Маршрут для обраотки запросов связанных с разделом blog- статьи articles:
Route::resource('articles','ArticlesController', [
                                                        'parameters' => [
                                                            'articles' => 'alias'
                                                        ]
]);

//Маршрут на записи конкретной категории
Route::get('articles/cat/{cat_alias?}', [
                            'uses' => 'ArticlesController@index',
                            'as' => 'articlesCat'
])->where('cat_alias','[\w-]+');


//Маршрут для записи коментария в БД
Route::resource('comment', 'CommentController', ['only' => [
                                                                        'store'
]]);

//Маршрут для страницы контактов:
Route::match(['get', 'post'], '/contacts', ['uses' => 'ContactsController@index', 'as' => 'contacts']);


Route::get('login', 'Auth\AuthController@showLoginForm');

Route::post('login', 'Auth\AuthController@login');

Route::get('logout', 'Auth\AuthController@logout');

//admin
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function (){

    //admin
    Route::get('/', ['uses' => 'Admin\IndexController@index',
                          'as'  => 'adminIndex'
    ]);

    //Управление статьями:
    Route::resource('/articles', 'Admin\ArticlesController');

    //Управление портфолио:
    Route::resource('/portfolios', 'Admin\PortfoliosController');

    //Управление разрешениями:
    Route::resource('/permissions', 'Admin\PermissionsController');

    //Управление разделами меню:
    Route::resource('/menus', 'Admin\MenusController');

    //Управление разделом пользователей:
    Route::resource('/users', 'Admin\UsersController');

});


