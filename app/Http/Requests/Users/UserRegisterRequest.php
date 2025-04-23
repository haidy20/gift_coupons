<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;


class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => [
                'required',
                // 'required_if:role,provider',
                'nullable',
                'unique:users_accounts,email',
            ],
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|unique:users_accounts,phone',
            'countries_id' => 'required|exists:countries,id',
            'agree_terms' => 'required|accepted' // ✅ يجب أن يكون موجودًا وقيمته true

        ];
    }

    
}
