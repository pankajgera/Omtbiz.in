<?php
namespace Crater\Http\Controllers;

use Illuminate\Http\Request;
use Crater\Country;

class LocationController extends Controller
{
<<<<<<< HEAD

    /**
     * Retrive a list of Countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
=======
>>>>>>> b7cd4d4c92eb822c2c1930072dceeafcc38c7c9d
    public function getCountries()
    {
        return response()->json([
            'countries' => Country::all()
        ]);
    }
}
