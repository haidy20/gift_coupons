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
            return response()->json(['message' => 'about not found'], 404);
        }

        $translation = $about->translation($locale);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'about retrived successfully',
            'data' => new UserShowAboutUsResource($translation),
        ], 201);
    }
}
