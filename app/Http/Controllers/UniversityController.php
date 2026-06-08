<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\HasUniversityFilters;
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
        $universities = $this->filteredUniversities($request, 40);

        return view('shared.universities.index', compact('universities', 'countries'));
    }

    public function create()
    {
        return view('shared.universities.create');
    }

    public function store(StoreUniversityRequest $request)
    {
        $university = new University($request->only([
            'name', 'short_name', 'country', 'city', 'website', 'contact_email', 'description'
        ]));

        if ($request->hasFile('university_logo')) {
            $file = $request->file('university_logo');
            $extension = $file->getClientOriginalExtension();
            $safeShortName = str_replace(' ', '_', strtolower($request->short_name ?? 'university'));
            $file->storeAs('uni_logo', $safeShortName . '.' . $extension, 'public');
            $university->university_logo = $safeShortName . '.' . $extension;
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
            'name', 'short_name', 'country', 'city', 'website', 'contact_email', 'description'
        ]));

        if ($request->hasFile('university_logo')) {
            if ($university->university_logo && Storage::disk('public')->exists('uni_logo/' . $university->university_logo)) {
                Storage::disk('public')->delete('uni_logo/' . $university->university_logo);
            }
            $file = $request->file('university_logo');
            $extension = $file->getClientOriginalExtension();
            $safeShortName = str_replace(' ', '_', strtolower($request->short_name ?? 'university'));
            $file->storeAs('uni_logo', $safeShortName . '.' . $extension, 'public');
            $university->university_logo = $safeShortName . '.' . $extension;
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
}
