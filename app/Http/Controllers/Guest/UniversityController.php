<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\University;

class UniversityController extends Controller
{
    public function index()
    {
        $universities = University::paginate(10); // Paginator, 10 per page
        return view('guest.universities.index', compact('universities'));
    }

    public function show($id)
    {
        $university = University::findOrFail($id);
        return view('guest.universities.show', compact('university'));
    }
}
