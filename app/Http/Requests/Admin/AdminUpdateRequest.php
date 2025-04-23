<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => 'sometimes|required|string|max:255',
            'email' => 'nullable|email|unique:users_accounts,email,' . $this->route('id'),
            'password' => 'sometimes|required|string|min:8|confirmed',
            'phone' => 'nullable|unique:users_accounts,phone,' . $this->route('id'),
            'countries_id' => 'sometimes|nullable|exists:countries,id',
            'latitude' => 'sometimes|nullable|numeric',
            'longitude' => 'sometimes|nullable|numeric',
            'location' => 'sometimes|nullable|string|max:255',
            'category_id' => 'sometimes|nullable|exists:categories,id',
            'role_id' => 'nullable|exists:roles,id',
            'image' => 'sometimes|image|mimes:jpg,jpeg,png,gif|max:2048', // ✅ تأكد من وجود هذا السطر

        ];
    }
}
