<?php

namespace App\Http\Requests\GeneralSetting;

use Illuminate\Foundation\Http\FormRequest;

class GeneralSettingStoreRequest extends FormRequest
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
            'application_name' => 'required|string|max:255',
            'footer_text' => 'required|string|max:500',
            'address' => 'required|string|max:500',
            'map_key' => 'required|string',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'school_about' => 'required|string',
            'default_langauge' => 'required|string|max:20',
            'session' => 'required',
            'currency_code' => 'required|string|max:10',
            'light_logo' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'dark_logo' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ];
    }
}
