<?php

namespace App\Http\Controllers;

use App\Services\HasUniversityFilters;
use App\Http\Requests\StoreUniversityRequest;
use App\Models\Course;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UniversityController extends Controller
{
    use AuthorizesRequests, HasUniversityFilters;

    public function __construct()
    {
        $this->authorizeResource(University::class, 'university');
    }

    public function index(Request $request)
    {
        $countries = University::select('country')->distinct()->pluck('country');
        $universities = $this->filteredUniversities($request, 60);

        return view('shared.universities.index', compact('universities', 'countries'));
    }

    public function create()
    {
        return view('shared.universities.create');
    }

    public function store(StoreUniversityRequest $request)
    {
        $university = new University($request->only([
            'name',
            'short_name',
            'country',
            'city',
            'website',
            'contact_email',
            'description'
        ]));

        if ($request->hasFile('university_logo')) {
            $logoName = $this->resolveLogoName($request->short_name ?: $request->name, $request->file('university_logo'));
            $request->file('university_logo')->storeAs('uni_logo', $logoName, 'public');
            $university->university_logo = $logoName;
        }

        $university->save();

        return redirect()->route(auth()->user()->role . '.universities.index')
            ->with('success', 'University added successfully.');
    }

    public function show(University $university)
    {
        $university->load('courses');
        return view('shared.universities.show', compact('university'));
    }

    public function edit(University $university)
    {
        $university->load('courses');
        return view('shared.universities.edit', compact('university'));
    }

    public function update(StoreUniversityRequest $request, University $university)
    {
        $university->fill($request->only([
            'name',
            'short_name',
            'country',
            'city',
            'website',
            'contact_email',
            'description'
        ]));

        if ($request->hasFile('university_logo')) {
            if ($university->university_logo && Storage::disk('public')->exists('uni_logo/' . $university->university_logo)) {
                Storage::disk('public')->delete('uni_logo/' . $university->university_logo);
            }
            $baseName = $request->short_name ?: $university->short_name ?: $request->name ?: $university->name;
            $logoName = $this->resolveLogoName($baseName, $request->file('university_logo'));
            $request->file('university_logo')->storeAs('uni_logo', $logoName, 'public');
            $university->university_logo = $logoName;
        }

        $university->save();

        return redirect()->route(auth()->user()->role . '.universities.show', $university)
            ->with('success', 'University updated successfully.');
    }

    public function destroy(University $university)
    {
        if ($university->university_logo && Storage::disk('public')->exists('uni_logo/' . $university->university_logo)) {
            Storage::disk('public')->delete('uni_logo/' . $university->university_logo);
        }
        $university->courses()->delete();
        $university->delete();

        return redirect()->route(auth()->user()->role . '.universities.index')
            ->with('success', 'University deleted successfully.');
    }

    /**
     * Build a safe, shortname-based filename for a university logo,
     * preserving the uploaded image's original extension.
     */
    protected function resolveLogoName(string $baseName, $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: 'png');
        $base = preg_replace('/[^A-Za-z0-9_-]/', '_', trim($baseName) ?: 'university');
        $base = trim($base, '_-');
        $base = $base ?: 'university';
        return $base . '.' . $extension;
    }
}
