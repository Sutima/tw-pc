<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => 'web'], function() {
    if (isset($_GET['system'])) {
        Route::get('/', ['middleware' => 'auth', function() {
            return view('pages.tripwire');
        }]);
    } else {
        Route::get('/', function () {
            return view('pages.landing');
        });
    }

    Route::group(['prefix' => 'register'], function() {
        Route::post('user', 'RegisterController@registerUser');
    });

    Route::get('logout', 'LoginController@logout');

    Route::group(['prefix' => 'login'], function() {
        Route::post('user', ['uses' => 'LoginController@userLogin', 'middleware' => 'loginThrottle:user']);
    });
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});
