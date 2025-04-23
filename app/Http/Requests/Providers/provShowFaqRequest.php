<?php

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;

class provShowFaqRequest extends FormRequest
{
    public function authorize()
    {
        // التحقق من وجود Accept-Language في الهيدر
        if (!$this->header('Accept-Language')) {
            return false; // رفض الطلب إذا لم يكن موجودًا
        }
        return true;
    }

    public function messages()
    {
        return [
            'id.required' => 'The term ID is required.',
            'id.exists' => 'The specified term ID does not exist.',
        ];
    }
}
