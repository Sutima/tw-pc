<?php

namespace App\Http\Middleware;

use DB;
use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Verify authentic user & update
        $account = collect(DB::select('SELECT admin FROM characters WHERE userID = :userID',
            ['userID' => session('userID')]))->first();
        if ($account) {
        	session(['admin' => $account->admin]);
        } else {
            return redirect('.');
        }


        return $next($request);
    }
}
