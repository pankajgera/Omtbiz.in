<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyContext
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user('api') ?? $request->user();

        if (!$user || !$user->company_id) {
            return response()->json([
                'error' => 'company_context_missing',
            ], 403);
        }

        $companyId = (int) $user->company_id;

        // The header remains available for legacy controllers, but the client
        // can no longer choose its value.
        $request->headers->set('company', (string) $companyId);
        $request->attributes->set('company_id', $companyId);

        return $next($request);
    }
}
