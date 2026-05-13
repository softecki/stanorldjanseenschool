<?php

namespace App\Http\Requests\Fees\Type;

use Illuminate\Foundation\Http\FormRequest;

class FeesTypeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|max:255|unique:fees_types',
            'code'        => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|in:0,1,2',
            'class_id'    => 'nullable|integer|min:0',
            'student_category_ids'   => 'nullable|array',
            'student_category_ids.*' => 'integer|exists:student_categories,id',
        ];
    }
}
