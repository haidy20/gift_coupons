<?php

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;

class ProvGetUsersVoucherStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true; // السماح بالوصول
    }

    public function rules()
    {
        return [
            'status' => 'required|in:active,used,expired',
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'The status field is required.',
            'status.in' => 'Invalid status. Allowed values: active, used, expired.',
        ];
    }
}
