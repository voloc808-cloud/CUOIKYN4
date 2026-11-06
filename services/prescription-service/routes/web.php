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

$router->group(['prefix' => 'api/v1', 'middleware' => ['jwt']], function () use ($router): void {
    $router->get('/prescriptions', 'PrescriptionController@index');
    $router->get('/prescriptions/{id:[0-9]+}', 'PrescriptionController@show');
    $router->post('/prescriptions', ['middleware' => 'role:DOCTOR', 'uses' => 'PrescriptionController@store']);
    $router->post('/prescriptions/{id:[0-9]+}/approve', ['middleware' => 'role:PHARMACIST', 'uses' => 'PrescriptionController@approve']);
    $router->post('/prescriptions/{id:[0-9]+}/reject', ['middleware' => 'role:PHARMACIST', 'uses' => 'PrescriptionController@reject']);
});

$router->group(['prefix' => 'internal/v1', 'middleware' => ['jwt', 'internal']], function () use ($router): void {
    $router->get('/reports/prescriptions', 'ReportDataController@prescriptions');
});
