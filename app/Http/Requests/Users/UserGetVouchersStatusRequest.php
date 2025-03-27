<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserGetVouchersStatusRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Change to false if you need authorization checks
    }

    public function rules()
    {
        return [
            'status' => 'required|string|in:active,used,expired',
        ];
    }
}
