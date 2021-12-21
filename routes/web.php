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

    $router->group(['prefix' => 'agenda'], function () use ($router) {
        $router->get('', 'AgendasController@get');
        $router->post('', 'AgendasController@create');
        $router->put('update/{id}', 'AgendasController@update');
        $router->delete('delete/{id}', 'AgendasController@delete');
    });

    $router->group(['prefix' => 'chat'], function () use ($router) {
        $router->get('', 'ChatsController@get');
        $router->get('history', 'ChatsController@history');
        $router->post('', 'ChatsController@create');
        $router->put('endchat/{id}', 'ChatsController@endChat');
        $router->put('ratechat/{id}', 'ChatsController@rateChat');
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
        $router->put('change-password/{id}', 'UserController@changepassword');
        $router->delete('delete/{id}', 'UserController@delete');
    });

    $router->group(['prefix' => 'message_attachments'], function () use ($router) {
        $router->get('', 'MessageAttachments@get');
        $router->post('', 'MessageAttachments@create');
        $router->delete('delete/{id}', 'MessageAttachments@delete');
    });

    $router->group(['prefix' => 'message_receivers'], function () use ($router) {
        $router->get('', 'MessageReceiversController@get');
        $router->put('update/{id}', 'MessageReceiversController@update');
        $router->delete('delete/{id}', 'MessageReceiversController@delete');
        $router->put('read_message', 'MessageReceiversController@read_message');
    });

    $router->group(['prefix' => 'message'], function () use ($router) {
        $router->get('', 'MessagesController@get');
        $router->post('', 'MessagesController@create');
        $router->put('update/{id}', 'MessagesController@update');
        $router->delete('delete/{id}', 'MessagesController@delete');
        $router->get('receiver/{id}', 'MessagesController@receiver');
        $router->get('inbox/{id}', 'MessagesController@inbox');
        $router->get('outbox/{id}', 'MessagesController@outbox');
        $router->put('delete_outbox/{id}', 'MessagesController@deleteOutbox');
        $router->delete('delete_inbox/{id}', 'MessagesController@deleteInbox');
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

    $router->group(['prefix' => 'daily_report'], function () use ($router) {
        $router->get('', 'DailyReportController@get');
        $router->get('download', 'DailyReportController@downloadSummary');
        $router->get('get_by_date', 'DailyReportController@getByDate');
        $router->post('', 'DailyReportController@create');
        $router->put('update/{id}', 'DailyReportController@update');
        $router->delete('delete/{id}', 'DailyReportController@delete');
    });

    $router->group(['prefix' => 'public_document'], function () use ($router) {
        $router->get('', 'PublicDocumentController@get');
        $router->post('', 'PublicDocumentController@create');
        $router->put('update/{id}', 'PublicDocumentController@update');
        $router->delete('delete/{id}', 'PublicDocumentController@delete');
    });

    $router->group(['prefix' => 'planning_schedule'], function () use ($router) {
        $router->get('', 'PlanningScheduleController@get');
        $router->post('', 'PlanningScheduleController@create');
        $router->put('update/{id}', 'PlanningScheduleController@update');
        $router->delete('delete/{id}', 'PlanningScheduleController@delete');
    });
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');

    $router->group(['prefix' => 'citizen_report'], function () use ($router) {
        $router->get('', 'CitizenReportController@get');
        $router->post('', 'CitizenReportController@create');
        $router->put('update/{id}', 'CitizenReportController@update');
        $router->delete('delete/{id}', 'CitizenReportController@delete');
    });

    $router->group(['prefix' => 'role'], function () use ($router) {
        $router->get('', 'RoleController@get');
        $router->post('', 'RoleController@create');
        $router->put('update/{id}', 'RoleController@update');
        $router->delete('delete/{id}', 'RoleController@delete');
    });

    $router->group(['prefix' => 'gallery'], function () use ($router) {
        $router->get('', 'GalleriesController@get');
        $router->post('', 'GalleriesController@create');
        $router->put('update/{id}', 'GalleriesController@update');
        $router->delete('delete/{id}', 'GalleriesController@delete');
    });

});
