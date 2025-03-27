<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserCreateFeedbackRequest extends FormRequest
{
    public function authorize()
    {
        return true; // تأكيد السماح لجميع المستخدمين المسجلين بإرسال الفيدباك
    }

    public function rules()
    {
        return [
            'rating' => 'required|integer|min:1|max:5',
            'description' => 'required|string|min:3',
        ];
    }

    public function messages()
    {
        return [
            'rating.required' => 'Rating is required.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Rating cannot be less than 1.',
            'rating.max' => 'Rating cannot be greater than 5.',
            'description.required' => 'Description is required.',
            'description.string' => 'Description must be a valid text.',
            'description.min' => 'Description must be at least 3 characters.',
        ];
    }
}
