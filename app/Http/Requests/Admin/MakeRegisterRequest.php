<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class MakeRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'email' => [
                'nullable',
                'unique:users_accounts,email',
            ],
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|unique:users_accounts,phone',
            'countries_id' => 'required|exists:countries,id',
            'latitude' => 'nullable|numeric',  // إضافة القواعد الخاصة بالإحداثيات
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id', // يجب أن يكون معرف الفئة موجودًا في جدول الفئات
        ];
    }
}
