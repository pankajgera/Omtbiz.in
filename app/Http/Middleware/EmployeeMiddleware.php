<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class EmployeeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->guest() || !Auth::user()->isEmployee()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized Emp.', 401);
            } else {
                return response()->json(['error' => 'user_is_not_admin'], 404);
            }
        }

        return $next($request);
    }
}
