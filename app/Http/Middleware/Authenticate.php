<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        /*if (! $request->expectsJson()) {
            return route('login');
        }*/
    }
    
    public function handle($request, Closure $next, ...$guards)
    {
        if(sizeof($guards) > 0 ){
            if (Auth::guard($guards[0])->guest()) {
                abort(401, 'Unauthorized');            
            }
        }
        
        return $next($request);
    }
}
