<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Credits;

class CreditsController extends Controller
{
    public function getHistory($id) {
        $credits = Credits::where('account_ledger_id', $id)->get();
        return response()->json($credits);
    }
    
    
}
