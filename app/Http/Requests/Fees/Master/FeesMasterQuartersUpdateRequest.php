<?php

namespace App\Http\Requests\Fees\Master;

use Illuminate\Foundation\Http\FormRequest;

class FeesMasterQuartersUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amounts' => ['required', 'array', 'size:4'],
            'amounts.0' => ['required', 'numeric', 'min:0'],
            'amounts.1' => ['required', 'numeric', 'min:0'],
            'amounts.2' => ['required', 'numeric', 'min:0'],
            'amounts.3' => ['required', 'numeric', 'min:0'],
        ];
    }
}
