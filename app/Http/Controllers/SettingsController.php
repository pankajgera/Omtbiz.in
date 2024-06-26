<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{

    /**
     * Retrive App Version.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAppVersion(Request $request)
    {
        $version = Setting::getSetting('version');

        return response()->json([
            'version' => $version,
        ]);
    }

}
