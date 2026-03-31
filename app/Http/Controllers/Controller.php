<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function adminOnlyResponse()
    {
        $user = auth()->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'error' => 'admin_only',
            ], 403);
        }

        return null;
    }
}
