<?php

namespace App\Http\Requests\Report;

use Illuminate\Foundation\Http\FormRequest;

class FeesCollectionRequest extends FormRequest
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
            'class'        => 'required',
            'section'      => 'required',
            'balance_status' => 'nullable',
            'fee_group_id' => 'nullable',
            'dates' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'payment_percentage' => 'nullable|in:10,20,30,40,50,60,70,80,90,100',
        ];
    }
}
