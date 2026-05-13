<?php

namespace App\Http\Requests\StudentInfo\ParentGuardian;

use App\Models\StudentInfo\ParentGuardian;
use Illuminate\Foundation\Http\FormRequest;

class ParentGuardianUpdateRequest extends FormRequest
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
        $parentId = $this->route('id');
        $userId = ParentGuardian::query()->whereKey($parentId)->value('user_id');
        $phoneRule = $userId
            ? 'required|max:255|unique:users,phone,'.$userId
            : 'required|max:255|unique:users,phone';

        return [
            'guardian_mobile'        => $phoneRule,
            'guardian_name'          => 'required|max:255',
            'status'                 => 'required|max:255',
            'father_name'            => 'nullable|max:255',
            'father_mobile'          => 'nullable|max:255',
            'father_profession'      => 'nullable|max:255',
            'father_nationality'     => 'nullable|max:255',
            'mother_name'            => 'nullable|max:255',
            'mother_mobile'          => 'nullable|max:255',
            'mother_profession'      => 'nullable|max:255',
            'guardian_profession'    => 'nullable|max:255',
            'guardian_email'         => 'nullable|email|max:255',
            'guardian_address'       => 'nullable|max:500',
            'guardian_relation'      => 'nullable|max:255',
        ];
    }
}
