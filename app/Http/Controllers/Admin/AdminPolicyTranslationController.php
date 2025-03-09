<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Policy;
use App\Models\PolicyTranslation;

// Requests
use App\Http\Requests\Admin\AdminPolicyTransRequest;
use App\Http\Requests\Admin\AdminShowPolicyRequest;

// Resources
use App\Http\Resources\Admin\AdminPolicyTransResource;
use App\Http\Resources\Admin\AdminShowPolicyResource;

class AdminPolicyTranslationController extends Controller
{
    // Admin onl can create  Terms
    public function create(AdminPolicyTransRequest $request)
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Only admins can create policy.', 'data' => null], 403);
        }
        // التحقق من البيانات باستخدام FormRequest
        $validatedData = $request->validated();
    
        // إنشاء المصطلح فقط دون تحديث
        $policy = Policy::create();
    
        // إدخال الترجمات 
        foreach (['en', 'ar'] as $locale) {
            PolicyTranslation::create([
                'policy_id' => $policy->id,
                'locale' => $locale,
                'title' => $validatedData["title_{$locale}"],
                'description' => $validatedData["description_{$locale}"],
            ]);
        }
    
        return response()->json([
            'success' => true,
            'message' => 'policy created successfully',
            'data' => new AdminPolicyTransResource($policy),
        ], 200);
    }
    
    

    public function show($id, AdminShowPolicyRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $policy = Policy::with('translations')->find($id);

        if (!$policy) {
            return response()->json([
                'status' => false,
                'message' => 'Policy not found',
                'data' => null
            ], 404);
        }

        $translation = $policy->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => false,
                'message' => 'Translation not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'policy retrived successfully',
            'data' => new AdminShowPolicyResource($translation),
        ], 200);
    }
}
