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

/*
* User RESTful resource
*/
$app->group([
    'prefix'    => 'user',
    'namespace' => 'App\Http\Controllers'
],
function() use ($app)
{
    $app->post('', [
        'as'   => 'user.store',
        'uses' => 'UserController@store',
    ]);
    $app->get('{id}', [
        'as'   => 'user.show',
        'uses' => 'UserController@show',
    ]);
    $app->put('{id}', [
        'as'   => 'user.update',
        'uses' => 'UserController@update',
    ]);
    $app->delete('{id}', [
        'as'   => 'user.destroy',
        'uses' => 'UserController@destroy',
    ]);
});

/**
 * Reset password routes
 */
$app->group([
    'prefix'    => 'password',
    'namespace' => 'App\Http\Controllers'
],
function() use ($app)
{
    $app->post('email', [
        'as'   => 'password.reset.email',
        'uses' => 'PasswordResetController@email',
    ]);
    $app->post('reset', [
        'as'   => 'password.reset.store',
        'uses' => 'PasswordResetController@store',
    ]);
});

/**
 * Session routes
 */
$app->group([
    'prefix'    => 'session',
    'namespace' => 'App\Http\Controllers'
],
function() use ($app)
{
    $app->post('login', [
        'as'   => 'session.login',
        'uses' => 'SessionController@login',
    ]);
    $app->delete('logout', [
        'as'   => 'session.logout',
        'uses' => 'SessionController@logout',
    ]);
});
