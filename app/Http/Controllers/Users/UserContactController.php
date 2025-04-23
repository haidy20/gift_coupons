<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\Country;

// Requests
use App\Http\Requests\Users\UsersCreateContactRequest;

// Responses
use App\Http\Resources\Users\UsersCreateContactResource;

class UserContactController extends Controller
{
    // Any one can contact
    public function create(UsersCreateContactRequest $request)
    {
        $existingContact = Contact::where('phone', $request->phone)
            ->where('countries_id', $request->countries_id)
            ->first();
        // dd( $request->phone);
        if ($existingContact) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.phone_with_country_code_exists'),
                'data' => null
            ], 400);
        }

        // Get the country code from the countries_codes table
        $country = Country::find($request->countries_id);
        // التحقق من وجود countryCode و phone_regex
        if (!$country || !$country->phone_regex) {
            return response()->json([
                'status'=>'fail',
                'message' => trans('messages.invalid_country_or_missing_phone_regex'),
                'data' => null,

            ], 400);
        }
        // Validate phone number against the regex of the selected country
        if (!preg_match("/{$country->phone_regex}/", $request->phone)) {
            return response()->json([
                'status'=>'fail',
                'message' => trans('messages.phone_country_mismatch'),
                'data' => null,
            ], 400);
        }
        // إنشاء ال contact الجديد
        $contact = Contact::create($request->validated());


        // إرجاع الرد باستخدام الـ Resource
        return response()->json([
            'status'=>'success',
            'message' => trans('messages.contact_created_successfully'),
            'data' => new UsersCreateContactResource($contact),
        ], 200);
    }
}
