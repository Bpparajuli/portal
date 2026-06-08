<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $allowedTypes = [
            'passport', '10th_certificate', '10th_transcript',
            '11th_transcript', '12th_certificate', '12th_transcript',
            'cv', 'moi', 'lor', 'ielts_pte_language_certificate'
        ];

        return [
            'document_type' => [
                'required',
                'string',
                Rule::in($allowedTypes),
                Rule::unique('documents', 'document_type')
                    ->where('student_id', $this->route('student')?->id)
                    ->whereRaw('LOWER(document_type) = ?', [strtolower($this->document_type)]),
            ],
            'file' => 'required|mimes:jpg,jpeg,png,pdf|max:51200',
        ];
    }
}
