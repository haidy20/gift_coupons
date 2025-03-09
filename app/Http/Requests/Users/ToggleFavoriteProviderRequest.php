<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class ToggleFavoriteProviderRequest extends FormRequest
{
    public function authorize()
    {
        return auth('api')->check();
    }

    public function rules()
    {
        return [
            'provider_id' => ['required', 'exists:users_accounts,id']
        ];
    }

    public function messages()
    {
        return [
            'provider_id.required' => 'The provider ID is required.',
            'provider_id.exists' => 'The selected provider does not exist.'
        ];
    }

}
