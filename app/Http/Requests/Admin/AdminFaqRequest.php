<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminFaqRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'question_en' => 'required|string',
            'answer_en' => 'required|string',
            'question_ar' => 'required|string',
            'answer_ar' => 'required|string',
        ];
    }
}
