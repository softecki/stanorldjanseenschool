<?php

namespace App\Http\Requests\Fees\Master;

use App\Enums\FineType;
use Illuminate\Foundation\Http\FormRequest;

class FeesMasterUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $fine = (int) $this->input('fine_type', FineType::NONE);

        $rules = [
            'fees_group_id' => 'required|integer|exists:fees_groups,id',
            'fees_type_id'  => 'required|integer|exists:fees_types,id',
            'due_date'      => 'required|date',
            'amount'        => 'required|numeric|min:0',
            'fine_type'     => 'required|integer|in:0,1,2',
            'status'        => 'required|in:0,1,2',
        ];

        if ($fine === FineType::PERCENTAGE) {
            $rules['percentage'] = 'required|integer|min:0|max:100';
            $rules['fine_amount'] = 'nullable|numeric|min:0';
        } elseif ($fine === FineType::FIX_AMOUNT) {
            $rules['fine_amount'] = 'required|numeric|min:0';
            $rules['percentage'] = 'nullable|integer|min:0|max:100';
        } else {
            $rules['percentage'] = 'nullable|integer|min:0|max:100';
            $rules['fine_amount'] = 'nullable|numeric|min:0';
        }

        return $rules;
    }
}
