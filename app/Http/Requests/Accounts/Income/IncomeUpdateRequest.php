<?php

namespace App\Http\Requests\Accounts\Income;

use Illuminate\Foundation\Http\FormRequest;

class IncomeUpdateRequest extends FormRequest
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
            'name'           => 'required|max:255',
            'income_head'    => 'required',
            'date'           => 'nullable|date',
            'amount'         => 'required|numeric|min:0.01'
        ];
    }
}
