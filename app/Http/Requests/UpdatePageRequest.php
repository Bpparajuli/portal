<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('pages')->ignore($this->route('page')),
            ],
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'template' => 'nullable|string|max:50',
            'is_published' => 'boolean',
            'is_menu_item' => 'boolean',
            'menu_order' => 'nullable|integer|min:0',
            'status' => 'nullable|in:draft,published,archived',
        ];
    }
}
