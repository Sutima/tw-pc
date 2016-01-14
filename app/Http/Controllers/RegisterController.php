<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Illuminate\Http\Request;
use App\Libraries\EVE_XML_API;

class RegisterController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Register Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles registration requests and creates users and
	| EVE characters if all conditions are met.
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
		$valid = $this->validateUserRequest($request);
		if ($valid['result'] !== true) {
			return response()->json($valid, 400);
		}

		$hashedPassword = Hash::make($request->input('password'));

		$createUser = DB::insert("INSERT INTO accounts (username, password) VALUES (:username, :password)",
			['username' => $request->input('username'), 'password' => $hashedPassword]);

		$lastInsertId = DB::connection()->getPdo()->lastInsertId();

		$createCharacter = DB::insert("INSERT INTO characters
			(userID, characterID, characterName, corporationID, corporationName)
			VALUES
			(:userID, :characterID, :characterName, :corporationID, :corporationName)",
			['userID' => $lastInsertId,
			'characterID' => $valid['character']->characterID,
			'characterName' => $valid['character']->characterName,
			'corporationID' => $valid['character']->corporationID,
			'corporationName' => $valid['character']->corporationName]
		);

		return response()->json(array('created' => true));
	}

	private function validateUserRequest(Request $request)
	{
		$output['result'] = false;

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
		$usernames = DB::select("SELECT username FROM accounts WHERE username = :username",
			['username' => $request->input('username')]);
		if (!empty($usernames)) {
			$output['field'] = 'username';
			$output['error'] = 'Username already taken';
			return $output;
		}

		// EVE API work
		$api = new EVE_XML_API;

		// Verify the API key access mask is exactly 'Account Status' (33554432)
		if ($api->checkMask($request->input('api_key'), $request->input('api_code'), 33554432) === 0) {
			$output['field'] = 'api';
			$output['error'] = 'Requires \'Account Status\' mask only! Cached until: ' . $api->cachedUntil;
			return $output;
		}

		$characters = $api->getCharacters($request->input('api_key'), $request->input('api_code'));
		$selected = $request->input('selected') ? $request->input('selected') : key($characters);

		// When API has more then 1 character and one hasn't been selected then show them
		if (empty($request->input('selected')) && count($characters) > 1) {
			$output['characters'] = $characters;
			return $output;
		}

		$character = collect(DB::select("SELECT characterID, ban FROM characters WHERE characterID = :characterID",
			['characterID' => $characters[$selected]->characterID]))->first();

		// Check if character is banned
		if ($character && $character->ban == true) {
			$output['field'] = count($characters) > 1 ? 'select' : 'api';
			$output['error'] = 'Character '.$characters[$selected]->characterName.' is banned.';
			return $output;
		}

		// Check if character is already assigned to an account
		if (count($character)) {
			$output['field'] = count($characters) > 1 ? 'select' : 'api';
			$output['error'] = 'Character '.$characters[$selected]->characterName.' already assigned to an account.';
			return $output;
		}

		$output['result'] = true;
		$output['character'] = $characters[$selected];
		return $output;
	}

}
