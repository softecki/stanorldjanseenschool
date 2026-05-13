<?php

namespace App\Http\Requests\StudentInfo\Student;

use Illuminate\Foundation\Http\FormRequest;

class StudentUpdateRequest extends FormRequest
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
        $mobile = '';
//        if (Request()->mobile != '') {
//            $mobile = 'max:255|unique:users,phone,'.$this->user_id;
//        }

//        $email = '';
//        if (Request()->email != '') {
//            $email = 'max:255|unique:users,email,'.$this->user_id;
//        }
       
        return [
            'id'                        => 'required|integer|exists:students,id',
            'first_name'                => 'required|max:255',
            'last_name'                 => 'required|max:255',
            'class'                     => 'required|max:255',
            'section'                   => 'required|max:255',
            'mobile'                    => 'required|max:255',
            'status'                    => 'required|max:255',
            'admission_no'              => 'nullable|max:255',
            'roll_no'                   => 'nullable|max:255',
            'email'                     => 'nullable|max:255',
            'date_of_birth'             => 'nullable|date',
            'admission_date'            => 'nullable|date',
            'religion'                  => 'nullable|max:255',
            'gender'                    => 'nullable|max:255',
            'blood_group'               => 'nullable|max:255',
            'category'                  => 'nullable|max:255',
            'previous_school'          => 'nullable|max:255',
            'previous_school_info'     => 'nullable|string',
            'residance_address'        => 'nullable|string',
            'place_of_birth'           => 'nullable|max:255',
            'nationality'              => 'nullable|max:255',
            'cpr_no'                   => 'nullable|max:255',
            'spoken_lang_at_home'      => 'nullable|max:255',
            'sms_send'                 => 'nullable|max:10',
            'sms_send_description'     => 'nullable|string',
            'control_number'           => 'nullable|max:255',
            'shift_id'                 => 'nullable|max:255',
        ];
        
    }
}
