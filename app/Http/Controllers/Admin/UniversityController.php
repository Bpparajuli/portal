<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use App\Models\University;
use Illuminate\Support\Facades\Storage;


class UniversityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Only authenticated users
    }

    /**
     * Display paginated list of universities with courses.
     */
    public function index(Request $request)
    {
        $countries = University::select('country')->distinct()->pluck('country');
        $query = University::with('courses');

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%$keyword%")
                    ->orWhereHas('courses', fn($qc) => $qc->where('title', 'like', "%$keyword%"));
            });
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        if ($request->filled('university_id')) {
            $query->where('id', $request->university_id);
        }

        if ($request->filled('course_id')) {
            $query->whereHas('courses', fn($q) => $q->where('id', $request->course_id));
        }

        $universities = $query->paginate(16)->withQueryString();
        return view('admin.universities.index', compact('universities', 'countries'));
    }



    public function getCities($country)
    {
        $cities = University::where('country', $country)
            ->select('city')->distinct()->pluck('city');
        return response()->json($cities);
    }

    public function getUniversities($city)
    {
        $unis = University::where('city', $city)
            ->select('id', 'name')->get();
        return response()->json($unis);
    }

    public function getCourses($universityId)
    {
        $courses = Course::where('university_id', $universityId)
            ->select('id', 'title')->get();
        return response()->json($courses);
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
            'university_logo' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:5120',
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
            $file = $request->file('university_logo');
            $extension = $file->getClientOriginalExtension();
            $safeShortName = str_replace(' ', '_', strtolower($request->short_name ?? 'university'));
            $logoName = $safeShortName . '.' . $extension;

            // Store logo in storage/app/public/uni_logo
            $file->storeAs('uni_logo', $logoName, 'public');

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
            'university_logo' => 'nullable|mimetypes:image/jpeg,image/png,image/gif,image/webp|max:5120',
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
            $file = $request->file('university_logo');
            $extension = $file->getClientOriginalExtension();
            $safeShortName = str_replace(' ', '_', strtolower($request->short_name ?? 'university'));
            $logoName = $safeShortName . '.' . $extension;

            // Delete old logo if exists
            if ($university->university_logo && Storage::disk('public')->exists('uni_logo/' . $university->university_logo)) {
                Storage::disk('public')->delete('uni_logo/' . $university->university_logo);
            }

            // Store new logo
            $file->storeAs('uni_logo', $logoName, 'public');
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
        if ($university->university_logo && Storage::disk('public')->exists('uni_logo/' . $university->university_logo)) {
            Storage::disk('public')->delete('uni_logo/' . $university->university_logo);
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
