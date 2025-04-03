<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Models\TermTranslation;
use Illuminate\Http\Request;

// Requests
use App\Http\Requests\Admin\AdminTermTransRequest;
use App\Http\Requests\Admin\AdminShowTermRequest;

// Resources
use App\Http\Resources\Admin\AdminTermTransResource;
use App\Http\Resources\Admin\AdminShowTermResource;



class AdminTermTranslationController extends Controller
{
    // Admin onl can create  Terms
    public function create(AdminTermTransRequest $request)
    {

        $admin = auth('api')->user();
        if ($admin->role !== 'superAdmin') {
            return response()->json(['status' => 'error', 'message' => 'Only superAdmin can create terms.', 'data' => null], 403);
        }
        // التحقق من البيانات باستخدام FormRequest
        $validatedData = $request->validated();
    
        // إنشاء المصطلح فقط دون تحديث
        $term = Term::create();
    
        // إدخال الترجمات
        foreach (['en', 'ar'] as $locale) {
            TermTranslation::create([
                'term_id' => $term->id,
                'locale' => $locale,
                'title' => $validatedData["title_{$locale}"],
                'description' => $validatedData["description_{$locale}"],
            ]);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Term created successfully',
            'data' => new AdminTermTransResource($term),
        ], 200);
    }
    
    

    public function show($id, AdminShowTermRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $term = Term::with('translations')->find($id);

        if (!$term) {
            return response()->json([
                'status' => false,
                'message' => 'Term not found',
                'data' => null
            ], 404);
        }


        $translation = $term->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => false,
                'message' => 'Translation not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Term retrived successfully',
            'data' => new AdminShowTermResource($translation),
        ], 200);
    }
}
