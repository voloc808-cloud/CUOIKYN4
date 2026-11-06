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

$router->group(['prefix' => 'api/v1', 'middleware' => ['jwt', 'role:PHARMACIST']], function () use ($router): void {
    $router->get('/reports/stock', 'ReportController@index');
    $router->get('/reports/stock.xlsx', 'ReportController@excel');
    $router->get('/reports/stock.pdf', 'ReportController@pdf');
});
