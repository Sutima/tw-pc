<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

//Route::get('/', 'WelcomeController@index');

$app->get('/', function()
{
    return view('pages.landing');
});

$app->group(['prefix' => 'register'], function ($app) {
    $app->post('user', 'App\Http\Controllers\RegisterController@registerUser');
});
