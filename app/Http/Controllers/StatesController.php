<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;

class StatesController extends Controller
{
    public function index()
    {
        $states = State::get(['code', 'name']);

        return response()->json([
            'states' => $states,
        ]);
    }
}
