<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UniversityController extends Controller
{
    //
    public function index()
    {
        $universities = University::all();
        return view('agent.universities.index', compact('universities'));
    }

    public function show(University $university)
    {
        return view('agent.universities.show', compact('university'));
    }
}
