<?php

namespace App\Http\Controllers;

use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    /**
     * Display a listing of universities with their courses.
     */
    public function index()
    {
        // Eager load courses to avoid N+1 problem, paginate
        $universities = University::with('courses')
            ->orderBy('name', 'asc')
            ->paginate(5);

        return view('universities.index', compact('universities'));
    }

    /**
     * Show the form for creating a new university.
     */
    public function create()
    {
        return view('universities.create');
    }

    /**
     * Store a newly created university in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'short_name'    => 'nullable|string|max:100',
            'country'       => 'required|string|max:100',
            'city'          => 'nullable|string|max:100',
            'website'       => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'description'   => 'nullable|string',
        ]);

        University::create($request->all());

        return redirect()
            ->route('universities.index')
            ->with('success', 'New university added successfully!');
    }

    /**
     * Show the form for editing the specified university.
     */
    public function edit($id)
    {
        $university = University::findOrFail($id);
        return view('universities.edit', compact('university'));
    }

    /**
     * Update the specified university in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'short_name'    => 'nullable|string|max:100',
            'country'       => 'required|string|max:100',
            'city'          => 'nullable|string|max:100',
            'website'       => 'nullable|url|max:255',
            'contact_email' => 'nullable|email|max:255',
            'description'   => 'nullable|string',
        ]);

        $university = University::findOrFail($id);
        $university->update($request->all());

        return redirect()
            ->route('universities.index')
            ->with('success', 'University updated successfully!');
    }

    /**
     * Remove the specified university from storage.
     */
    public function destroy($id)
    {
        $university = University::findOrFail($id);

        // Optionally delete related courses before deleting university
        $university->courses()->delete();

        $university->delete();

        return redirect()
            ->route('universities.index')
            ->with('success', 'University deleted successfully!');
    }
}
