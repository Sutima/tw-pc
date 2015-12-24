<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegisterController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		#$this->middleware('guest');
	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function registerUser(Request $request)
	{
		if (strlen($request->input('username')) < 5) {
			$output['field'] = 'username';
			$output['error'] = 'Username must be at least 5 characters long';
			return response()->json($output);
		}
	}

}
