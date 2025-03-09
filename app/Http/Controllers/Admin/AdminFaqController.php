<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTranslation;

use Illuminate\Http\Request;
// Requests
use App\Http\Requests\Admin\AdminFaqRequest;
use App\Http\Requests\Admin\AdminShowFaqRequest;

// Resources
use App\Http\Resources\Admin\AdminFaqResource;
use App\Http\Resources\Admin\AdminShowFaqResource;


class AdminFaqController extends Controller
{
    public function index()
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can create FAQs.',
                'data' => null,
            ], 403);
        }
        
        $faqs = Faq::with('translations')->get();
        return response()->json([
            'status' => true,
            'message' => 'FAQs retrieved successfully',
            'data' => AdminFaqResource::collection($faqs),
        ], 200);
    }

    /**
     * إضافة سؤال جديد مع الترجمات
     */
    public function create(AdminFaqRequest $request)
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can create FAQs.',
                'data' => null,
            ], 403);
        }

        $faq = Faq::create();

        foreach (['en', 'ar'] as $locale) {
            FaqTranslation::create([
                'faq_id' => $faq->id,
                'locale' => $locale,
                'question' => $request->input("question_{$locale}"),
                'answer' => $request->input("answer_{$locale}"),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'FAQ created successfully',
            'data' => new AdminFaqResource($faq),
        ], 201);
    }


    public function show($id, AdminShowFaqRequest $request)
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can view FAQs.',
                'data' => null,
            ], 403);
        }
    
        $locale = $request->header('Accept-Language', 'en');
        $faq = Faq::with('translations')->find($id);
    
        if (!$faq) {
            return response()->json([
                'status' => false,
                'message' => 'FAQ not found',
                'data' => null,
            ], 404);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'FAQ retrieved successfully',
            'data' => new AdminShowFaqResource($faq, $locale), // تمرير اللغة
        ], 200);
    }
    
    /**
     * تحديث سؤال موجود
     */
    public function update(AdminFaqRequest $request, $id)
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can update FAQs.',
                'data' => null,
            ], 403);
        }

        $faq = Faq::findOrFail($id);

        foreach (['en', 'ar'] as $locale) {
            $translation = FaqTranslation::where('faq_id', $faq->id)->where('locale', $locale)->first();
            
            if ($translation) {
                $translation->update([
                    'question' => $request->input("question_{$locale}"),
                    'answer' => $request->input("answer_{$locale}"),
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'FAQ updated successfully',
            'data' => new AdminFaqResource($faq),
        ], 200);
    }

    /**
     * حذف سؤال معين
     */
    public function destroy($id)
    {
        $admin = auth('api')->user();
        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Only admins can delete FAQs.',
                'data' => null,
            ], 403);
        }

        $faq = Faq::findOrFail($id);
        $faq->delete();

        return response()->json([
            'status' => true,
            'message' => 'FAQ deleted successfully',
            'data' => null,
        ], 200);
    }
}
