<?php

namespace App\Http\Controllers\Guest;

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
        $universities = $this->filteredUniversities($request, 15);

        return view('guest.universities.index', compact('universities', 'countries'));
    }

    public function show(University $university)
    {
        $university = $this->loadUniversity($university);
        return view('guest.universities.show', compact('university'));
    }
}
