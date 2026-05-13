<?php

namespace App\Http\Requests\StudentInfo\ParentGuardian;

use Illuminate\Foundation\Http\FormRequest;

class ParentGuardianStoreRequest extends FormRequest
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
            'guardian_mobile'        => 'required|max:255|unique:users,phone',
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
