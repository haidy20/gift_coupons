<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ToggleFavoriteVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow the request to proceed
    }

    public function rules(): array
    {
        return [
            'voucher_id' => 'required|exists:vouchers,id',
        ];
    }
}
