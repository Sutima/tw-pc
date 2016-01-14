<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class EveStatusController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| EVE Status Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles EVE status requests.
	| Get the server status from database
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//$this->middleware('App\Http\Middleware\loginThrottle');
    }

    public function get(Request $request)
    {
		$result = collect(DB::select('SELECT players, status AS online, time FROM eve_api.serverStatus ORDER BY time DESC LIMIT 1'))->first();

		return response()->json($result);
	}

}
