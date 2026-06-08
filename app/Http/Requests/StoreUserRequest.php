<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'contact' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:admin,agent,staff,university,student',
            'status' => 'nullable|boolean',
            'password' => 'required|min:6|confirmed',
            'parent_id' => 'required_if:role,staff|nullable|exists:users,id',
            'agreement_status' => 'nullable|in:not_uploaded,uploaded,verified',
            'business_logo' => 'nullable|file|max:20480',
            'registration' => 'nullable|file|max:20480',
            'pan' => 'nullable|file|max:20480',
            'agreement_file' => 'nullable|file|max:20480',
        ];
    }
}
