<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AboutUs;
use App\Models\AboutUsTranslation;

// Requests
use App\Http\Requests\Admin\AdminAboutUsTransRequest;
use App\Http\Requests\Admin\AdminShowAboutUsRequest;

// Resources
use App\Http\Resources\Admin\AdminAboutUsTransResource;
use App\Http\Resources\Admin\AdminShowAboutUsResource;


class AdminAboutUsTranslationController extends Controller
{
    // Admin onl can create  Terms
    public function create(AdminAboutUsTransRequest $request)
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        // التحقق من البيانات باستخدام FormRequest
        $validatedData = $request->validated();
        // إنشاء المصطلح فقط دون تحديث
        $about = AboutUs::create();

        // إدخال الترجمات 
        foreach (['en', 'ar'] as $locale) {
            AboutUsTranslation::create([
                'about_id' => $about->id,
                'locale' => $locale,
                'title' => $validatedData["title_{$locale}"],
                'description' => $validatedData["description_{$locale}"],
            ]);
        }

        return response()->json([
            'success' => 'success',
            'message' => trans('messages.about_created_successfully'),
            'data' => new AdminAboutUsTransResource($about),
        ], 200);
    }



    public function show($id, AdminShowAboutUsRequest $request)
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
            'data' => new AdminShowAboutUsResource($translation),
        ], 200);
    }
}
