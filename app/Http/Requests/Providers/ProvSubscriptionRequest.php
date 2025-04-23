<?php

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;

class ProvSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ✅ Allows all authenticated users to make the request
    }

    public function rules(): array
    {
        return [
            'subscription_id' => 'required|exists:subscriptions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'subscription_id.required' => 'Subscription ID is required.',
            'subscription_id.exists' => 'Selected subscription does not exist.',
        ];
    }
}
