<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\University;
use Illuminate\Support\Facades\Validator;

class UniversityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Ensure only authenticated users can access
    }

    /**
     * Index universities with their courses (paginated).
     */
    public function index()
    {
        $universities = University::with('courses')
            ->orderBy('name', 'asc')
            ->paginate(10);

        return view('admin.universities.index', compact('universities'));
    }

    /**
     * Show form to create a new university.
     */
    public function create()
    {
        return view('admin.universities.create');
    }

    /**
     * Store a new university.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'short_name'      => 'nullable|string|max:100',
            'country'         => 'required|string|max:100',
            'city'            => 'nullable|string|max:100',
            'website'         => 'nullable|url|max:255',
            'contact_email'   => 'nullable|email|max:255',
            'description'     => 'nullable|string',
            'university_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $university = new University($request->only([
            'name',
            'short_name',
            'country',
            'city',
            'website',
            'contact_email',
            'description'
        ]));

        // Handle university logo upload
        if ($request->hasFile('university_logo')) {
            $extension = $request->file('university_logo')->getClientOriginalExtension();
            $safeShortName = str_replace(' ', '_', strtolower($request->short_name ?? 'university'));
            $logoName = $safeShortName . '.' . $extension;

            $destinationPath = public_path('images/uni_logo');

            // Make directory if it doesn't exist
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move new logo to folder
            $request->file('university_logo')->move($destinationPath, $logoName);
            $university->university_logo = $logoName;
        }

        $university->save();

        return redirect()
            ->route('admin.universities.index')
            ->with('success', 'New university added successfully!');
    }

    /**
     * Show form to edit a university.
     */
    public function edit($id)
    {
        $university = University::with('courses')->findOrFail($id);
        return view('admin.universities.edit', compact('university'));
    }

    /**
     * Update a university.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'short_name'      => 'nullable|string|max:100',
            'country'         => 'required|string|max:100',
            'city'            => 'nullable|string|max:100',
            'website'         => 'nullable|url|max:255',
            'contact_email'   => 'nullable|email|max:255',
            'description'     => 'nullable|string',
            'university_logo' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $university = University::findOrFail($id);
        $university->fill($request->only([
            'name',
            'short_name',
            'country',
            'city',
            'website',
            'contact_email',
            'description'
        ]));

        // Handle university logo upload
        if ($request->hasFile('university_logo')) {
            $extension = $request->file('university_logo')->getClientOriginalExtension();
            $safeShortName = str_replace(' ', '_', strtolower($request->short_name ?? 'university'));
            $logoName = $safeShortName . '.' . $extension;

            $destinationPath = public_path('images/uni_logo');

            // Delete old logo if exists
            if ($university->university_logo && file_exists($destinationPath . '/' . $university->university_logo)) {
                unlink($destinationPath . '/' . $university->university_logo);
            }

            // Move new logo to folder
            $request->file('university_logo')->move($destinationPath, $logoName);
            $university->university_logo = $logoName;
        }

        $university->save();

        return redirect()
            ->route('admin.universities.index')
            ->with('success', 'University updated successfully!');
    }

    /**
     * Delete a university and its courses.
     */
    public function destroy($id)
    {
        $university = University::findOrFail($id);

        // Delete logo if exists
        $logoPath = public_path('images/uni_logo/' . $university->university_logo);
        if ($university->university_logo && file_exists($logoPath)) {
            unlink($logoPath);
        }

        // Delete related courses
        $university->courses()->delete();

        $university->delete();

        return redirect()
            ->route('admin.universities.index')
            ->with('success', 'University deleted successfully!');
    }

    /**
     * Show university profile page.
     */
    public function show($id)
    {
        $university = University::with('courses')->findOrFail($id);
        return view('admin.universities.show', compact('university'));
    }
}
