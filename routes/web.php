<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'auth', 'prefix' => 'api'], function ($router) {
    $router->get('me', 'AuthController@me');

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('', 'UserController@get');
    });

    $router->group(['prefix' => 'opd'], function () use ($router) {
        $router->get('', 'RolesOpdsController@get');
        $router->post('', 'RolesOpdsController@create');
        $router->put('update/{id}', 'RolesOpdsController@update');
        $router->delete('delete/{id}', 'RolesOpdsController@delete');
    });
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    $router->group(['prefix' => 'role'], function () use ($router) {
        $router->get('', 'RoleController@get');
        $router->post('', 'RoleController@create');
        $router->put('update/{id}', 'RoleController@update');
        $router->delete('delete/{id}', 'RoleController@delete');
    });
});
