<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\University;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function welcome(Request $request)
    {
        $countries = University::select('country')->distinct()->pluck('country');

        return view('guest.dashboard', compact('countries'));
    }
}
