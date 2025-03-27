<?php

namespace App\Http\Requests\Providers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;


class ProvUpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('api')->check();
    }

    public function rules(): array
    {
        $user = $this->user();
        $regex = $this->getPhoneRegex();
    
        return [
            'username' => 'nullable|string|max:255',
            // 'email'=> 'nullable|unique:users_accounts,email',
            // 'phone' => "nullable|string|max:20|unique:users_accounts,phone,{$user->id}|regex:{$regex}",
            'email' => "nullable|unique:users_accounts,email,{$user->id},id",
            'phone' => "nullable|string|max:20|unique:users_accounts,phone,{$user->id},id|regex:{$regex}",


            'countries_id' => 'nullable|exists:countries,id',
            'category_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    private function getPhoneRegex(): string
    {
        $countryCode = $this->input('countries_id');
        $country = DB::table('countries')->where('id', $countryCode)->first();

        if (!$country || !$country->phone_regex) {
            return '/^.*$/'; // Default regex to match any input
        }

        $regex = $country->phone_regex;
        if ($regex &&!preg_match('/^.*\/.*$/', $regex)) {
            $regex = '/' . $regex . '/';
        }

        return $regex;
    }
}
