<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class LoginController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Login Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles login requests.
	| Login via Tripwire username & password
    | Login via EVE API key to match to Tripwire character
    | Login via EVE SSO (to be implimented)
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		//$this->middleware('App\Http\Middleware\loginThrottle');
		//$output['result'] = $route;
		//return response()->json($output);
    }

    public function userLogin(Request $request)
    {
		// Require a username
        if (empty($request->input('username'))) {
            $output['field'] = 'username';
            $output['error'] = 'Username is required.';
            return response()->json($output, 400);
        }

		// Require a password
        if (empty($request->input('password'))) {
            $output['field'] = 'password';
            $output['error'] = 'Password is required.';
            return response()->json($output, 400);
        }

		// Prevent long passwords that can cause excessive server load from password hashing
        if (strlen($request->input('password')) > 72) {
            $output['field'] = 'password';
            $output['error'] = 'Password is too long.';
            return response()->json($output, 400);
        }

		$account = collect(app('db')->select('SELECT id, username, password, accounts.ban, characterID, characterName, corporationID, corporationName, admin, super, options FROM accounts LEFT JOIN preferences ON id = preferences.userID LEFT JOIN characters ON id = characters.userID WHERE username = :username',
			['username' => $request->input('username')]))->first();

		// Check if account username exists
		if (empty($account)) {
			$output['field'] = 'password';
			$output['error'] = 'Incorrect username or password.';
			return response()->json($output, 400);
		}

		// Check if account has been banned
		if ($account->ban == true) {
			$output['field'] = 'password';
			$output['error'] = 'Account has been banned.';
			return response()->json($output, 400);
		}

		// Check if password is correct
		if (!app('hash')->check($request->input('password'), $account->password)) {
			$output['field'] = 'password';
			$output['error'] = 'Incorrect username or password.';
			return response()->json($output, 400);
		}

		$request->session()->put('userID', $account->id);
		$request->session()->put('admin', $account->admin);
		$request->session()->put('super', $account->super);

		/*
		$_SESSION['userID'] = $account->id; // Y
		$_SESSION['username'] = $account->username;
		$_SESSION['ip'] = $ip;
		$_SESSION['mask'] = @$options->masks->active ? $options->masks->active : $account->corporationID . '.2';
		$_SESSION['characterID'] = $account->characterID;
		$_SESSION['characterName'] = $account->characterName;
		$_SESSION['corporationID'] = $account->corporationID;
		$_SESSION['corporationName'] = $account->corporationName;
		$_SESSION['admin'] = $account->admin; // Y
		$_SESSION['super'] = $account->super; // Y
		$_SESSION['options'] = $options;
		*/

		return response()->json(array('result' => 'success'));
    }

    public function apiLogin(Request $request)
    {

    }

    public function ssoLogin(Request $request)
    {

    }

	public function logout(Request $request)
	{
		session()->flush();
		return redirect('.');
	}

}
