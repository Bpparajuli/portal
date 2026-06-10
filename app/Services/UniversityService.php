<?php
namespace App\Services;

use App\Models\University;
use Illuminate\Http\Request;

class UniversityService
{
    /**
     * Get paginated universities with optional search and country filter.
     */
    public function getFilteredUniversities(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = University::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        return $query->orderBy('name')->paginate(20);
    }
}
