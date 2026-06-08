<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'gender' => 'nullable|in:Male,Female,Other',
            'dob' => 'nullable|date',
            'marital_status' => 'nullable|in:Single,Married,Other',
            'permanent_address' => 'nullable|string|max:500',
            'temporary_address' => 'nullable|string|max:500',
            'nationality' => 'nullable|string|max:100',
            'passport_number' => 'nullable|string|max:100',
            'passport_expiry' => 'nullable|date',
            'applying_for' => 'nullable|string|max:255',
            'qualification' => 'nullable|string|max:255',
            'passed_year' => 'nullable|integer|min:1950|max:2099',
            'gap' => 'nullable|integer|min:0',
            'last_grades' => 'nullable|string|max:50',
            'education_board' => 'nullable|string|max:100',
            'preferred_country' => 'nullable|string|max:100',
            'preferred_city' => 'nullable|string|max:100',
            'preferred_course' => 'nullable|string|max:255',
            'preferred_university' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'rating' => 'nullable|integer|min:1|max:5',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'expected_revenue' => 'nullable|numeric|min:0',
            'current_stage_id' => 'nullable|exists:student_stages,id',
            'students_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}
