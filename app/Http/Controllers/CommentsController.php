<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class CommentsController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Comments Controller
	|--------------------------------------------------------------------------
	|
	| This controller handles comment requests.
	| Save creates and updates a comment
    | Delete removes a comment
    | Sticky removes the systemID so the comment shows on all systems
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
		// Require a maskID
        if (empty($request->input('maskID'))) {
            $output['error'] = 'A mask ID is required.';
            return response()->json($output, 400);
        }

		// Require a commentID
        if (empty($request->input('commentID'))) {
            $output['error'] = 'A comment ID is required.';
            return response()->json($output, 400);
        }

		$comment = DB::select('',
			['' => $request->input('')])
	}

	public function getAll(Request $request)
	{
		// Require a maskID
        if (empty($request->input('maskID'))) {
            $output['error'] = 'A mask ID is required.';
            return response()->json($output, 400);
        }

		// Require an EVE solarSystemID
        if (empty($request->input('systemID'))) {
            $output['error'] = 'A system ID is required.';
            return response()->json($output, 400);
        }

		$comments = DB::select('',
			['' => $request->input('')])
	}

    public function save(Request $request)
    {
		// Require a maskID
        if (empty($request->input('maskID'))) {
            $output['error'] = 'A mask ID is required.';
            return response()->json($output, 400);
        }

		// Require an EVE solarSystemID
        if (empty($request->input('systemID'))) {
            $output['error'] = 'A system ID is required.';
            return response()->json($output, 400);
        }

		// Require an EVE characterID
        if (empty($request->input('characterID'))) {
            $output['error'] = 'A character ID is required.';
            return response()->json($output, 400);
        }

		// Require a comment body
        if (empty($request->input('comment'))) {
            $output['error'] = 'Comment is required.';
            return response()->json($output, 400);
        }

		$result = DB::insert('INSERT INTO comments (id, systemID, comment, created, createdBy, modifiedBy, maskID)
					VALUES (:commentID, :systemID, :comment, NOW(), :createdBy, :modifiedBy, :maskID)
					ON DUPLICATE KEY UPDATE
					systemID = :systemID, comment = :comment, modifiedBy = :modifiedBy, modified = NOW()',
			['commentID' => $request->input('commentID')
			,'systemID' => $request->input('systemID'
			,'comment' => $request->input('comment')
			,'createdBy' => $request->input('characterID')
			,'modifiedBy' => $request->input('characterID')
			,'maskID' => $request->input('maskID'))]);


    }

	public function delete(Request $request)
	{
		// Require a maskID
        if (empty($request->input('maskID'))) {
            $output['error'] = 'A mask ID is required.';
            return response()->json($output, 400);
        }

		// Require a commentID
        if (empty($request->input('commentID'))) {
            $output['error'] = 'A comment ID is required.';
            return response()->json($output, 400);
        }

		$result = DB::delete('DELETE FROM comments WHERE id = :commentID AND maskID = :maskID',
			['commentID' => $request->input('commentID'), 'maskID' => $request->input('maskID')]);

		return response()->json(array('result' => $result));
	}

	public function sticky(Request $request)
	{
		// Require a maskID
        if (empty($request->input('maskID'))) {
            $output['error'] = 'A mask ID is required.';
            return response()->json($output, 400);
        }

		// Require a commentID
        if (empty($request->input('commentID'))) {
            $output['error'] = 'A comment ID is required.';
            return response()->json($output, 400);
        }

		$result = DB::update('UPDATE comments SET systemID = NULL WHERE id = :commentID AND maskID = :maskID',
			['commentID' => $request->input('commentID'), 'maskID' => $request->input('maskID')]);

		return response()->json(array('result' => $result));
	}

}
