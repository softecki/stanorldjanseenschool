<?php

namespace App\Http\Requests\StudentInfo\Student;

use Illuminate\Foundation\Http\FormRequest;

class StudentStoreRequest extends FormRequest
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
     * array:21 [▼ // app\Http\Controllers\StudentInfo\StudentController.php:79
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 'admission_no'              => 'required|max:255|unique:students,admission_no',
            // 'roll_no'                   => 'required|max:255',
            'first_name'                => 'required|max:255',
            'last_name'                 => 'required|max:255',
            'mobile'                    => 'required|max:255',
            'class'                     => 'required|max:255',
            'section'                   => 'required|max:255',
//            'date_of_birth'             => 'required|max:255',
            // 'admission_date'            => 'required|max:255',
            // 'parent'                    => 'required|max:255',
            // 'status'                    => 'required|max:255'
        ];


    }
}
