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

$router->group(['prefix' => 'api/v1/auth'], function () use ($router): void {
    $router->post('/login', 'AuthController@login');
    $router->group(['middleware' => 'jwt'], function () use ($router): void {
        $router->post('/logout', 'AuthController@logout');
        $router->put('/change-password', 'AuthController@changePassword');
    });
});

$router->group([
    'prefix' => 'api/v1/users',
    'middleware' => ['jwt', 'role:ADMIN'],
], function () use ($router): void {
    $router->get('/', 'UserController@index');
    $router->post('/', 'UserController@store');
    $router->get('/{id:[0-9]+}', 'UserController@show');
    $router->put('/{id:[0-9]+}', 'UserController@update');
    $router->patch('/{id:[0-9]+}/status', 'UserController@status');
    $router->delete('/{id:[0-9]+}', 'UserController@destroy');
});
