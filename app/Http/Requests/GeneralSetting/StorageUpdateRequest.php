<?php

namespace App\Http\Requests\GeneralSetting;

use Illuminate\Foundation\Http\FormRequest;

class StorageUpdateRequest extends FormRequest
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
        if ($this->input('file_system') === 's3') {
            return [
                'file_system' => 'required|in:local,s3',
                'aws_access_key_id' => 'required|string|max:500',
                'aws_secret_key' => 'required|string|max:500',
                'aws_region' => 'required|string|max:100',
                'aws_bucket' => 'required|string|max:255',
                'aws_endpoint' => 'required|string|max:500',
            ];
        }

        return [
            'file_system' => 'required|in:local,s3',
        ];
    }
}
