<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminSubscriptionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // ✅ السماح بالطلب
    }

    public function rules()
    {
        return [
            'is_active' => 'sometimes|boolean',
            'translations' => 'required|array',
            'translations.*.locale' => 'required|string|in:en,ar',
            'translations.*.title' => 'required|string',
            'translations.*.description' => 'nullable|string',
            'translations.*.price' => 'required|numeric',
            'translations.*.duration' => 'required|string',
        ];
    }
}
