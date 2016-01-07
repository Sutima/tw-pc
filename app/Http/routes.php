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

$app->get('/', function() {
    if (isset($_GET['system'])) {
        return view('pages.tripwire');
    } else {
        return view('pages.landing');
    }
});

$app->get('logout', ['uses' => 'LoginController@logout']);

$app->group(['prefix' => 'register', 'namespace' => 'App\Http\Controllers'], function ($register) {
    $register->post('user', 'RegisterController@registerUser');
});

$app->group(['prefix' => 'login', 'namespace' => 'App\Http\Controllers'], function ($login) {
    $login->post('user', ['uses' => 'LoginController@userLogin', 'middleware' => 'App\Http\Middleware\loginThrottle:user']);
});
