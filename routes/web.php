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

$router->group(['middleware' => ['auth', 'verified'], 'prefix' => 'api'], function ($router) {

    $router->get('me', 'AuthController@me');
    $router->post('/email/request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('', 'UserController@get');
        $router->put('update/{id}', 'UserController@update');
        $router->put('change-password/{id}', 'UserController@changepassword');
        $router->delete('delete/{id}', 'UserController@delete');
    });

    $router->group(['prefix' => 'category'], function () use ($router) {
        $router->get('', 'CategoryController@get');
        $router->post('create', 'CategoryController@create');
        $router->put('update/{id}', 'CategoryController@update');
        $router->delete('delete/{id}', 'CategoryController@delete');
        $router->post('import', 'CategoryController@import');
        $router->post('selected_action/delete', 'CategoryController@selectedDelete');
        $router->get('selected_action/export_excel', 'CategoryController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'CategoryController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'CategoryController@importExcel');
    });

    $router->group(['prefix' => 'product'], function () use ($router) {
        $router->get('', 'ProductController@get');
        $router->post('create', 'ProductController@create');
        $router->put('update/{id}', 'ProductController@update');
        $router->delete('delete/{id}', 'ProductController@delete');
    });
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->get('/email/verify', ['as' => 'email.verify', 'uses' => 'AuthController@emailVerify']);
});
