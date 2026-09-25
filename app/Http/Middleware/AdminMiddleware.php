<?php
namespace App\Http\Middleware;

use Auth;
use Closure;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::guard($guard)->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'error' => 'admin_only',
            ], 403);
        }

        return $next($request);
    }
}
