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

    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->post('', 'AuthController@registerAdmin');
        $router->get('', 'UserController@getAdmin');
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
        $router->get('{id}', 'ProductController@show');
        $router->post('selected_action/delete', 'ProductController@selectedDelete');
        $router->get('selected_action/export_excel', 'ProductController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'ProductController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'ProductController@importExcel');
    });

    $router->group(['prefix' => 'product-payment'], function () use ($router) {
        $router->get('', 'ProductPaymentController@get');
        $router->get('admin', 'ProductPaymentController@getAdmin');
        $router->post('create', 'ProductPaymentController@create');
        $router->delete('delete/{id}', 'ProductPaymentController@delete');
        $router->get('snap', 'ProductPaymentController@snapPayment');
        $router->get('check-status/{no_transaction}', 'ProductPaymentController@checkStatus');
        $router->post('check-status-snap', 'ProductPaymentController@statusSnap'); 
        $router->post('selected_action/delete', 'ProductPaymentController@selectedDelete');
        $router->get('selected_action/export_excel', 'ProductPaymentController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'ProductPaymentController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'ProductPaymentController@importExcel');
    });

    $router->group(['prefix' => 'product-result'], function () use ($router) {
        $router->get('', 'ProductResultController@get');
        $router->get('admin', 'ProductResultController@getAdmin');
        $router->post('create', 'ProductResultController@create');
        $router->put('update/{id}', 'ProductResultController@update');
        $router->get('{id}', 'ProductResultController@show');
        $router->post('selected_action/delete', 'ProductResultController@selectedDelete');
        $router->get('selected_action/export_excel', 'ProductResultController@selectedExportExcel');
        $router->get('selected_action/export_pdf', 'ProductResultController@selectedExportPdf');
        $router->post('selected_action/import_excel', 'ProductResultController@importExcel');
    });

    $router->group(['prefix' => 'dashboard'], function () use ($router) {
        $router->get('get-year', 'DashboardController@getYearTransaction');
        $router->get('grafik-volume-transaction/{tahun}', 'DashboardController@grafikVolumeTransaction');
        $router->get('grafik-volume-transaction-by-sim/{tahun}', 'DashboardController@grafikVolumeTransactionBySim');
        $router->get('grafik-volume-transaction-by-payment-method/{tahun}', 'DashboardController@grafikVolumeTransactionByPaymentMethod');
    });

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
});
