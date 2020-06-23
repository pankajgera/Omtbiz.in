<?php

namespace Crater\Http\Middleware;

use Auth;
use Closure;

class AccountantMiddleware
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
        if (Auth::guard($guard)->guest() || !Auth::user()->isAccountant()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized Acc.', 401);
            } else {
                return response()->json(['error' => 'user_is_not_admin'], 404);
            }
        }

        return $next($request);
    }
}
