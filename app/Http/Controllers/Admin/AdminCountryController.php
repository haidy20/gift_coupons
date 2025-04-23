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
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $countries = Country::all();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.countries_retrieved_successfully'),
            'data' => AdminCountryResource::collection($countries)
        ], 200);
    }

    public function create(AdminCountryRequest $request)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $country = Country::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.country_created_successfully'),
            'data' => new AdminCountryResource($country),
        ], 200);
    }

    public function show($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.country_not_found'),
                'data' => null
            ], 404); // إرجاع رسالة خطأ في حالة عدم وجود الكود
        }
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.country_retrieved_successfully'),
            'data' => new AdminCountryResource($country),
        ], 200);
    }

    public function update(AdminCountryRequest $request, $id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $country = Country::find($id);
        if (!$country) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.country_not_found'),
                'data' => null
            ], 404); // إرجاع رسالة خطأ في حالة عدم وجود الكود
        }
        $validated = $request->validated();

        if ($country->country_name == 'country_name' && $request->has('country_code') && $validated['country_code'] != 'FixedCode') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_country_code'),
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
            'status' => 'success',
            'message' => trans('messages.country_updated_successfully'),
            'data' => new AdminCountryResource($country),
        ], 200);
    }

    public function destroy($id)
    {
        $user = auth('api')->user();
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $country = Country::find($id);

        if (!$country) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.country_not_found'),
                'data' => null
            ], 404);
        }
        $country->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.country_deleted_successfully'),
            'data' => null
        ], 200);
    }
}
