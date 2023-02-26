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

$router->get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});

// $router->group(['middleware' => ['auth', 'verified'], 'prefix' => 'api'], function ($router) {
$router->group(['middleware' => ['auth'], 'prefix' => 'api'], function ($router) {

    $router->get('me', 'AuthController@me');
    $router->post('change-password', 'AuthController@changePassword');
    $router->post('email/request-verification', ['as' => 'email.request.verification', 'uses' => 'AuthController@emailRequestVerification']);

    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->get('', 'UserController@get');
        $router->get('admin', 'UserController@getAdmin');
        $router->put('update/{id}', 'UserController@update');
        $router->put('change-password/{id}', 'UserController@changepassword');
        $router->delete('delete/{id}', 'UserController@delete');
        $router->post('selected_action/delete', 'UserController@selectedDelete');
        $router->get('selected_action/export_excel', 'UserController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'UserController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'UserController@importExcel');
    });

    $router->group(['prefix' => 'role'], function () use ($router) {
        $router->get('', 'RoleController@get');
        $router->post('create', 'RoleController@create');
        $router->put('update/{id}', 'RoleController@update');
        $router->delete('delete/{id}', 'RoleController@delete');
        $router->post('import', 'RoleController@import');
        $router->post('selected_action/delete', 'RoleController@selectedDelete');
        $router->get('selected_action/export_excel', 'RoleController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'RoleController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'RoleController@importExcel');
    });

    $router->group(['prefix' => 'menu'], function () use ($router) {
        $router->get('', 'MenuController@get');
        $router->post('create', 'MenuController@create');
        $router->put('update/{id}', 'MenuController@update');
        $router->delete('delete/{id}', 'MenuController@delete');
        $router->post('import', 'MenuController@import');
        $router->post('selected_action/delete', 'MenuController@selectedDelete');
        $router->get('selected_action/export_excel', 'MenuController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'MenuController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'MenuController@importExcel');
    });

    $router->group(['prefix' => 'document'], function () use ($router) {

        $router->post('create', 'DocumentController@create');
        $router->get('', 'DocumentController@get');
        $router->get('show/{id}', 'DocumentController@show');
        $router->delete('delete/{id}', 'DocumentController@delete');
        $router->put('update/{id}', 'DocumentController@update');
        $router->put('approveAdmin', 'DocumentController@approveAdmin');
        $router->put('approveLegalDrafter', 'DocumentController@approveLegalDrafter');
        $router->put('approveSuncang', 'DocumentController@approveSuncang');
        $router->put('approveKasubag', 'DocumentController@approveKasubag');
        $router->put('approveKabag', 'DocumentController@approveKabag');
        $router->put('approveAssistant', 'DocumentController@approveAssistant');
        $router->put('approveSekda', 'DocumentController@approveSekda');
    });

    $router->group(['prefix' => 'restorant'], function () use ($router) {
        $router->get('', 'RestorantController@get');
        $router->post('create', 'RestorantController@create');
        $router->put('update/{id}', 'RestorantController@update');
        $router->delete('delete/{id}', 'RestorantController@delete');
    });

    $router->group(['prefix' => 'dashboard'], function () use ($router) {
        $router->get('', 'DashboardController@get');
    });
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('login-admin', 'AuthController@loginAdmin');
    $router->get('email/verify', ['as' => 'email.verify', 'uses' => 'AuthController@emailVerify']);
    $router->post('transaction/callback', 'ProductPaymentController@callback');
    $router->post('email/forget-password', ['as' => 'email.forget.password', 'uses' => 'AuthController@emailForgetPassword']);
    $router->get('email/reset-password', ['as' => 'email.reset.password', 'uses' => 'AuthController@emailResetPassword']);
    $router->post('reset-password', ['uses' => 'AuthController@submitEmailResetPassword']);

    $router->group(['prefix' => 'donasi'], function () use ($router) {
        $router->get('', 'DonasiController@get');
        $router->post('create', 'DonasiController@create');
        $router->put('update/{id}', 'DonasiController@update');
        $router->delete('delete/{id}', 'DonasiController@delete');
    });
});
