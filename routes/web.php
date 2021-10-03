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

    $router->group(['prefix' => 'gallery'], function () use ($router) {
        $router->get('', 'GalleriesController@get');
        $router->post('', 'GalleriesController@create');
        $router->put('update/{id}', 'GalleriesController@update');
        $router->delete('delete/{id}', 'GalleriesController@delete');
    });

    $router->group(['prefix' => 'agenda'], function () use ($router) {
        $router->get('', 'AgendasController@get');
        $router->post('', 'AgendasController@create');
        $router->put('update/{id}', 'AgendasController@update');
        $router->delete('delete/{id}', 'AgendasController@delete');
    });

    $router->group(['prefix' => 'chat'], function () use ($router) {
        $router->get('', 'ChatsController@get');
        $router->post('', 'ChatsController@create');
        $router->put('endchat/{id}', 'ChatsController@endchat');
        $router->delete('delete/{id}', 'ChatsController@delete');
    });

    $router->group(['prefix' => 'agenda_detail'], function () use ($router) {
        $router->get('', 'AgendaDetailsController@get');
        $router->post('', 'AgendaDetailsController@create');
        $router->put('update/{id}', 'AgendaDetailsController@update');
        $router->delete('delete/{id}', 'AgendaDetailsController@delete');
    });

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('', 'UserController@get');
        $router->put('update/{id}', 'UserController@update');
        $router->delete('delete/{id}', 'UserController@delete');
    });

    $router->group(['prefix' => 'message_attachments'], function () use ($router) {
        $router->get('', 'MessageAttachments@get');
        $router->post('', 'MessageAttachments@create');
        $router->delete('delete/{id}', 'MessageAttachments@delete');
    });

    $router->group(['prefix' => 'message_receivers'], function () use ($router) {
        $router->get('', 'MessageReceivers@get');
        $router->put('update/{id}', 'MessageReceivers@update');
        $router->delete('delete/{id}', 'MessageReceivers@delete');
    });

    $router->group(['prefix' => 'message'], function () use ($router) {
        $router->get('', 'MessagesController@get');
        $router->post('', 'MessagesController@create');
        $router->put('update/{id}', 'MessagesController@update');
        $router->delete('delete/{id}', 'MessagesController@delete');
    });

    $router->group(['prefix' => 'document_type'], function () use ($router) {
        $router->get('', 'DocumentTypesController@get');
        $router->post('', 'DocumentTypesController@create');
        $router->put('update/{id}', 'DocumentTypesController@update');
        $router->delete('delete/{id}', 'DocumentTypesController@delete');
    });

    $router->group(['prefix' => 'document'], function () use ($router) {
        $router->get('', 'DocumentsController@get');
        $router->post('', 'DocumentsController@create');
        $router->put('update/{id}', 'DocumentsController@update');
        $router->delete('delete/{id}', 'DocumentsController@delete');
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
