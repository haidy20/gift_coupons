<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminTermTransRequest extends FormRequest
{
    public function authorize()
    {
        return true; // أو ضع شرط الصلاحية هنا
    }

    public function rules()
    {
        return [
            'title_en' => 'required|string|max:255',
            'description_en' => 'required|string',
            'title_ar' => 'required|string|max:255',
            'description_ar' => 'required|string',
        ];
    }
}
