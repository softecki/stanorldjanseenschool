<?php

namespace App\Http\Requests\Fees\Assign;

use App\Enums\FineType;
use Illuminate\Foundation\Http\FormRequest;

class FeesAssignUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'fees_group'             => 'required|max:25',
            'class'                  => 'required|max:25',
            'assignment_lines'       => 'nullable|array',
            'assignment_lines.*.fees_master_id' => 'required_with:assignment_lines|integer',
            'assignment_lines.*.student_ids'      => 'nullable|array',
            'assignment_lines.*.student_ids.*'  => 'integer',
            // Legacy layout (create / blades): flat student ids
            'student_ids'                 => 'nullable|array',
            'student_ids.*'               => 'integer',
            'fees_master_ids'             => 'nullable|array',
            'fees_master_ids.*'           => 'integer',
        ];
    }
}
