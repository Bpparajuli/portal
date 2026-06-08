<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasUniversityFilters;
use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    use HasUniversityFilters;

    public function index(Request $request)
    {
        $countries = University::select('country')->distinct()->pluck('country');
        $universities = $this->filteredUniversities($request, 30);

        return view('agent.universities.index', compact('universities', 'countries'));
    }

    public function show(University $university)
    {
        $university = $this->loadUniversity($university);
        return view('agent.universities.show', compact('university'));
    }
}
