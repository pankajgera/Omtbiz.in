<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space\Updater;
use App\Models\Space\SiteApi;

class UpdateController extends Controller
{
    public function update(Request $request)
    {
        set_time_limit(600); // 10 minutes

        $json = Updater::update($request->installed, $request->version);

        return response()->json($json);
    }

    public function finishUpdate(Request $request)
    {
        $json = Updater::finishUpdate($request->installed, $request->version);

        return response()->json($json);
    }

    public function checkLatestVersion(Request $request)
    {
        set_time_limit(600); // 10 minutes

        $json = Updater::checkForUpdate();

        return response()->json($json);
    }
}
