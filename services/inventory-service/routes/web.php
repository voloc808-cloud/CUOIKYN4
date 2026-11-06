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

$router->group([
    'prefix' => 'api/v1',
    'middleware' => ['jwt', 'role:PHARMACIST'],
], function () use ($router): void {
    $router->get('/import-receipts', 'InventoryController@receipts');
    $router->post('/import-receipts', 'InventoryController@store');
    $router->get('/import-receipts/{id:[0-9]+}', 'InventoryController@receipt');
    $router->get('/batches', 'InventoryController@batches');
    $router->get('/stock-movements', 'InventoryController@movements');
    $router->get('/disposal-receipts', 'DisposalController@index');
    $router->post('/disposal-receipts', 'DisposalController@store');
    $router->get('/disposal-receipts/{id:[0-9]+}', 'DisposalController@show');
    $router->get('/alerts', 'AlertController@index');
    $router->put('/alert-settings', 'AlertController@update');
});

$router->group([
    'prefix' => 'internal/v1',
    'middleware' => ['jwt', 'internal'],
], function () use ($router): void {
    $router->get('/medicines/{medicineId:[0-9]+}/has-batches', 'InventoryController@hasBatches');
    $router->post('/stock/check', 'StockController@check');
    $router->post('/stock/deduct-fefo', 'StockController@deduct');
    $router->post('/stock/restore', 'StockController@restore');
    $router->get('/stock/prescriptions/{prescriptionId:[0-9]+}', 'StockController@status');
    $router->get('/reports/stock', 'ReportDataController@stock');
});
