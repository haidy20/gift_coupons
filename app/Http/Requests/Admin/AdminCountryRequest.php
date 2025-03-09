<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminCountryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
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
        $id = $this->route('id'); // جلب ID عند التحديث

        return [
            'country_name' => 'required|string|max:255|unique:countries,country_name,' . $id,
            'country_code' => 'required|string|max:10|unique:countries,country_code,' . $id,
            'phone_regex'  => 'nullable|string|max:255',
        ];
    }
}
