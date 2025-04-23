<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Faq;

// Requests
use App\Http\Requests\Providers\provShowFaqRequest;

// Responses
use App\Http\Resources\Providers\ProvShowFaqResource;

class ProvFaqController extends Controller
{
    // Anyone can see all FAQs
    public function index(provShowFaqRequest $request)
    {   
        $locale = $request->header('Accept-Language', 'en'); // تحديد اللغة الافتراضية كـ en
        $faqs = Faq::with('translations')->get();
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.faqs_retrieved_successfully'),
            'data' => ProvShowFaqResource::collection($faqs->map(fn($faq) => ['faq' => $faq, 'locale' => $locale])),
        ], 200);
    }
}
