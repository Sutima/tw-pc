<?php

namespace App\Http\Middleware;

use DB;
use Closure;

class LoginThrottle
{
    protected $throttle = array(6 => 3, 4 => 2, 2 => 1);

    public function handle($request, Closure $next, $mode)
    {
        // Check failed logins and throttle all login attempts
        $failedAttempts = count(DB::select('SELECT ip FROM _history_login WHERE DATE_ADD(time, INTERVAL 30 SECOND) > NOW()'));
        foreach ($this->throttle AS $attempts => $delay) {
            if ($failedAttempts > $attempts) {
                if (is_numeric($delay)) {
                    sleep($delay);
                } else {
                    // display recaptcha
                }
                break;
            }
        }

        // perform request
        $response = $next($request);

        // Log the login attempt
        DB::insert('INSERT INTO _history_login (ip, username, method, result) VALUES (:ip, :username, :method, :result)',
			['ip' => $request->ip(),
			 'username' => $request->input('username'),
			 'method' => $mode,
			 'result' => $response->status() == 200 ? 'success' : 'fail']);

        return $response;
    }
}
