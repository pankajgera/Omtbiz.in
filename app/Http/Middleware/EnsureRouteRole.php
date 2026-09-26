<?php

namespace App\Http\Middleware;

use App\Support\RouteRoles;
use Closure;
use Illuminate\Http\Request;

class EnsureRouteRole
{
    public function handle(Request $request, Closure $next)
    {
        $roles = RouteRoles::forAction($request->route()->getActionName());
        if (!in_array($request->user('api')?->role, $roles, true)) {
            return response()->json(['error' => $roles === ['admin'] ? 'admin_only' : 'role_forbidden'], 403);
        }

        return $next($request);
    }
}
