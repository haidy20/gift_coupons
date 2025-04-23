<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutUs;

// Requests
use App\Http\Requests\Providers\ProvShowAboutUsRequest;
// Resources
use App\Http\Resources\Providers\ProvShowAboutUsResource;

class ProvAboutUsController extends Controller
{
    public function show($id, ProvShowAboutUsRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $about = AboutUs::with('translations')->find($id);

        if (!$about) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.about_not_found'),
                'data' => null
            ], 404);
        }

        $translation = $about->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.translation_not_found'),
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.about_retrieved_successfully'),
            'data' => new ProvShowAboutUsResource($translation),
        ], 201);
    }
}
