<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AboutUs;

// Requests
use App\Http\Requests\Users\UserShowAboutUsRequest;

// Responses
use App\Http\Resources\Users\UserShowAboutUsResource;

class UserAboutUsController extends Controller
{
    public function show($id,UserShowAboutUsRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $about = AboutUs::with('translations')->find($id);

        if (!$about) {
            return response()->json([
                'status'=>'fail',
                'message' => trans('messages.about_not_found'),
                'data'=>null
            ], 404);
        }

        $translation = $about->translation($locale);

        if (!$translation) {
            return response()->json([
                'status'=>'fail',
                'message' => trans('messages.translation_not_found'),
                'data'=>null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.about_retrieved_successfully'),
            'data' => new UserShowAboutUsResource($translation),
        ], 201);
    }
}
