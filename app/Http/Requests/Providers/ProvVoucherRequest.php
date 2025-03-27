<?php

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;

class ProvVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return auth('api')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            // 'name' => 'required|string|unique:vouchers,name,' . $this->voucher,
            'name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'start_date' => 'required|date|after_or_equal:today',
            'duration_days' => 'required|integer|min:1',
        ];
    }
}
