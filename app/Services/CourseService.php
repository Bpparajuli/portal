<?php
namespace App\Services;

use App\Models\Course;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class CourseService
{
    public function getFilteredList(Request $request): LengthAwarePaginator
    {
        $user = request()->user();
        $query = Course::with('university');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('course_code', 'like', "%{$search}%")
                  ->orWhereHas('university', fn($u) => $u->where('name', 'like', "%{$search}%")
                      ->orWhere('short_name', 'like', "%{$search}%"));
            });
        }

        if ($country = $request->input('country')) {
            $query->whereHas('university', fn($q) => $q->where('country', $country));
        }

        if ($city = $request->input('city')) {
            $query->whereHas('university', fn($q) => $q->where('city', $city));
        }

        if ($courseType = $request->input('course_type')) {
            $query->where('course_type', $courseType);
        }

        return $query->orderBy('id', $user?->is_admin ? 'ASC' : 'DESC')
            ->paginate($user?->is_admin ? 50 : 20);
    }

    public function getIndexFilters(): array
    {
        return [
            'countries' => University::select('country')->distinct()->pluck('country'),
            'courseTypes' => Course::distinct()->pluck('course_type'),
        ];
    }

    public function getCreateData(?int $universityId = null): array
    {
        return [
            'universities' => University::orderBy('name')->get(),
            'selectedUniversity' => $universityId ? University::find($universityId) : null,
        ];
    }

    public function store(array $data): Course
    {
        return Course::create($data);
    }

    public function loadForShow(Course $course): Course
    {
        return $course->load('university');
    }

    public function loadForEdit(Course $course): array
    {
        $course->load('university');
        return [
            'course' => $course,
            'previous' => Course::where('id', '<', $course->id)->orderBy('id', 'desc')->first(),
            'next' => Course::where('id', '>', $course->id)->orderBy('id', 'asc')->first(),
            'universities' => University::orderBy('name')->get(),
        ];
    }

    public function update(Course $course, array $data): bool
    {
        return $course->update($data);
    }

    public function destroy(Course $course): ?bool
    {
        return $course->delete();
    }
}
