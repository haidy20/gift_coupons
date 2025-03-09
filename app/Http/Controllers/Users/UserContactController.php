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
        // return response()->json(['status' => 'error','message' => 'Only admins can create categories.','data' => null], 403);
        // تحقق من الف uniqueness للمجموعة (الرقم + الكود)
        $existingContact = Contact::where('phone', $request->phone)
            ->where('countries_id', $request->countries_id)
            ->first();
        // dd( $request->phone);
        if ($existingContact) {
            return response()->json(['status' => 'error','message' => 'This phone number with this country code already exists.','data' => null], 400);
        }

        // Get the country code from the countries_codes table
        $country = Country::find($request->countries_id);
        // التحقق من وجود countryCode و phone_regex
        if (!$country || !$country->phone_regex) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid country code or missing phone regex.',
                'data'=>null,

            ], 400);
        }
        // Validate phone number against the regex of the selected country
        if (!preg_match("/{$country->phone_regex}/", $request->phone)) {
            return response()->json([
                'success' => false,
                'message' => 'The phone number does not match the selected country code.',
                'data'=>null,
            ], 400);
        }
        // إنشاء ال contact الجديد
        $contact = Contact::create($request->validated());


        // إرجاع الرد باستخدام الـ Resource
        return response()->json([
            'success' => true,
            'message' => 'Contact created successfully!',
            'data' => new UsersCreateContactResource($contact),
        ], 200);
    }
}
