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
        if ($admin->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can create about us', 'data' => null], 403);
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
            'success' => true,
            'message' => 'about created successfully',
            'data' => new AdminAboutUsTransResource($about),
        ], 200);
    }
    
    

    public function show($id,AdminShowAboutUsRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $about = AboutUs::with('translations')->find($id);

        if (!$about) {
            return response()->json([
                'status' => false,
                'message' => 'About not found',
                'data' => null
            ], 404);
        }

        $translation = $about->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => false,
                'message' => 'Translation not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'about retrived successfully',
            'data' => new AdminShowAboutUsResource($translation),
        ], 200);
    }
}
