<?php

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Voucher;

class ProvSearchPurchasersRequest extends FormRequest
{ 
    
    public function authorize()
    {
        return auth('api')->check();
    }

    public function rules()
{
    return [
        'query' => [
            'nullable',
            'string',
            function ($attribute, $value, $fail) {
                if (!Voucher::where('id', $value)->exists()) {
                    $fail('The voucher ID does not exist.');
                    return; // Stop further validation if the voucher doesn't exist
                }

                $provider = auth('api')->user();
                if (!Voucher::where('id', $value)->where('provider_id', $provider->id)->exists()) {
                    $fail('You do not have access to this voucher.');
                }
            }
        ],
    ];
}


    public function messages()
    {
        return [
            'query.string' => 'The voucher ID must be a valid string.', // إذا كان الـ ID ليس نصيًا
            'query.exists' => 'The voucher ID does not exist.', // إذا كان الـ ID غير موجود
            'query.nullable' => 'The voucher ID can be empty, but if provided, it must be valid.', // في حالة تركه فارغًا
        ];
    }
}
