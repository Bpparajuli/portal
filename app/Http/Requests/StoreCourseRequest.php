<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'university_id' => 'required|exists:universities,id',
            'course_code' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'course_type' => 'required|in:UG,PG,DIPLOMA',
            'course_link' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'academic_requirement' => 'nullable|string',
            'duration' => 'nullable|string|max:255',
            'fee' => 'nullable|string|max:255',
            'intakes' => 'required|string|max:255',
            'ielts_pte_other_languages' => 'nullable|string',
            'moi_acceptance' => 'nullable|string',
            'application_fee' => 'nullable|string|max:255',
            'scholarships' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }
}
