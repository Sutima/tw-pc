<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Libraries\EVE_XML_API;

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
		$validation = $this->validateRequest($request);
		if ($validation !== true) {
			return response()->json($validation);
		}
	}

	private function validateRequest(Request $request)
	{
		// Require a username of at least 5 characters
		if (strlen($request->input('username')) < 5) {
			$output['field'] = 'username';
			$output['error'] = 'Username must be at least 5 characters long.';
			return $output;
		}

		// Require a password of at least 5 characters
		if (strlen($request->input('password')) < 5) {
			$output['field'] = 'password';
			$output['error'] = 'Password Must be at least 5 characters long.';
			return $output;
		}

		// Prevent long passwords that can cause excessive server load from password hashing
		if (strlen($request->input('password')) > 72) {
			$output['field'] = 'password';
			$output['error'] = 'Password is too long.';
			return $output;
		}

		// Make sure that both password fields match
		if (strcmp($request->input('password'), $request->input('confirm')) !== 0) {
			$output['field'] = 'password';
			$output['error'] = 'Passwords do not match.';
			return $output;
		}

		// Require an EVE XML API key ID
		if (empty($request->input('api_key'))) {
			$output['field'] = 'api';
			$output['error'] = 'API key required.';
			return $output;
		}

		// Require an EVE XML API vCode
		if (empty($request->input('api_code'))) {
			$output['field'] = 'api';
			$output['error'] = 'API vCode required.';
			return $output;
		}

		// Make sure the username isn't already taken
		$usernames = app('db')->select("SELECT username FROM accounts WHERE username = :username",
			['username' => $request->input('username')]);
		if (!empty($usernames)) {
			$output['field'] = 'username';
			$output['error'] = 'Username already taken';
			return $output;
		}

		// API
		$apiError = function ($message) {
			$output['field'] = 'api';
			$output['error'] = $message;
			return $output;
		};

		$api = new EVE_XML_API;
		//$api->apiError = $apiError;

		//return $api->checkMask($request->input('api_key'), $request->input('api_code'), 33554432);

		if ($api->checkMask($request->input('api_key'), $request->input('api_code')) != 33554432) {
			$output['field'] = 'api';
			$output['error'] = 'Requires \'Account Status\' mask only! Cached until: ' . $api->cachedUntil;
			return $output;
		}

		if ($api->apiError) {
			$output['field'] = 'api';
			$output['error'] = $api->apiError;
			return $output;
		}
	}

}
