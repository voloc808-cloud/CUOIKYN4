<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/health', 'HealthController');

$router->group(['prefix' => 'api/v1', 'middleware' => 'jwt'], function () use ($router): void {
    $router->get('/medicines', 'MedicineController@index');
    $router->get('/medicines/{id:[0-9]+}', 'MedicineController@show');
    $router->delete('/medicines/{id:[0-9]+}', 'MedicineController@destroyVerified');
    $router->get('/units', 'UnitController@index');

    $router->group(['middleware' => 'role:PHARMACIST'], function () use ($router): void {
        $router->post('/medicines', 'MedicineController@store');
        $router->put('/medicines/{id:[0-9]+}', 'MedicineController@update');
        $router->delete('/medicines/{id:[0-9]+}', 'MedicineController@destroy');
        $router->post('/units', 'UnitController@store');
        $router->get('/unit-conversions', 'UnitController@conversions');
        $router->post('/unit-conversions', 'UnitController@storeConversion');
        $router->get('/suppliers', 'SupplierController@index');
        $router->post('/suppliers', 'SupplierController@store');
        $router->get('/suppliers/{id:[0-9]+}', 'SupplierController@show');
        $router->put('/suppliers/{id:[0-9]+}', 'SupplierController@update');
        $router->delete('/suppliers/{id:[0-9]+}', 'SupplierController@destroy');
    });
});

$router->group([
    'prefix' => 'internal/v1',
    'middleware' => ['jwt', 'internal'],
], function () use ($router): void {
    $router->get('/medicines/stock-thresholds', 'MedicineController@stockThresholds');
    $router->get('/medicines/{id:[0-9]+}', 'MedicineController@show');
    $router->get('/suppliers/{id:[0-9]+}', 'SupplierController@show');
});
