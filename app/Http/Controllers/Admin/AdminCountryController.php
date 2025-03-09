<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
// Requests
use App\Http\Requests\Admin\AdminCountryRequest;
// Resources
use App\Http\Resources\Admin\AdminCountryResource;


class AdminCountryController extends Controller
{
    public function index()
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can get all country codes.', 'data' => null], 403);
        }
        $countries = Country::all();

        return response()->json([
            'success' => true,
            'message' => 'Country codes retrived successfully',
            'data' => AdminCountryResource::collection($countries)
        ], 200);
    }

    public function create(AdminCountryRequest $request)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can create country codes.', 'data' => null], 403);
        }

        $country = Country::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Country code created successfully',
            'data' => new AdminCountryResource($country),
        ], 200);
    }

    public function show($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can show country code.', 'data' => null], 403);
        }
        $country = Country::find($id);

        if (!$country) {
            return response()->json(['status' => 'error', 'message' => 'Country code does not exist', 'data' => null], 404); // إرجاع رسالة خطأ في حالة عدم وجود الكود
        }
        return response()->json([
            'success' => true,
            'message' => 'Country code retrived successfully',
            'data' => new AdminCountryResource($country),
        ], 200);
    }

    public function update(AdminCountryRequest $request, $id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can update country codes.', 'data' => null], 403);
        }

        $country = Country::find($id);
        if (!$country) {
            return response()->json(['status' => 'error', 'message' => 'Country code does not exist', 'data' => null], 404); // إرجاع رسالة خطأ في حالة عدم وجود الكود
        }
        $validated = $request->validated();

        if ($country->country_name == 'country_name' && $request->has('country_code') && $validated['country_code'] != 'FixedCode') {
            return response()->json([
                'status' => 'error',
                'message' => 'The country code for country name cannot be changed.',
                'data' => null
            ], 400);
        }

        if ($request->has('country_name')) {
            $country->country_name = $validated['country_name'];
        }

        if ($request->has('country_code')) {
            $country->country_code = '+' . $validated['country_code'];
        }

        // تحديث phone_regex إذا تم إدخاله
        if ($request->has('phone_regex')) {
            $country->phone_regex = $validated['phone_regex'];
        }

        $country->save();

        return response()->json([
            'success' => true,
            'message' => 'Country code updated successfully',
            'data' => new AdminCountryResource($country),
        ], 200);
    }

    public function destroy($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can delete countries.', 'data' => null], 403);
        }

        $country = Country::find($id);

        if (!$country) {
            return response()->json(['status' => 'error', 'message' => 'country not found', 'data' => null], 404);
        }
        $country->delete();

        return response()->json([
            'success' => true,
            'message' => 'Country code deleted successfully',
            'data' => null
        ], 200);
    }
}
