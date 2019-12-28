<?php

namespace Crater\Http\Controllers;

use Illuminate\Http\Request;
use Crater\Setting;

class SettingsController extends Controller
{
<<<<<<< HEAD

    /**
     * Retrive App Version.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
=======
>>>>>>> b7cd4d4c92eb822c2c1930072dceeafcc38c7c9d
    public function getAppVersion(Request $request)
    {
        $version = Setting::getSetting('version');

        return response()->json([
            'version' => $version,
        ]);
    }

}
