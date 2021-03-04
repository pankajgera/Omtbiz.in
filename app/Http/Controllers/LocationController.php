<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;

class LocationController extends Controller
{

    /**
     * Retrive a list of Countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCountries()
    {
        return response()->json([
            'countries' => Country::all()
        ]);
    }
}
