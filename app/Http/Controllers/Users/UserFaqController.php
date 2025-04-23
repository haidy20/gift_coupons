<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Faq;
// Requests
use App\Http\Requests\Users\UserShowFaqRequest;

// Responses
use App\Http\Resources\Users\UserShowFaqResource;

class UserFaqController extends Controller
{
    // Anyone can see all FAQs
    public function index(UserShowFaqRequest $request)
    {   
        $locale = $request->header('Accept-Language', 'en'); // تحديد اللغة الافتراضية كـ en
        $faqs = Faq::with('translations')->get();
        return response()->json([
            'status'=>'success',
            'message' => trans('messages.faqs_retrieved_successfully'),
            'data' => UserShowFaqResource::collection($faqs->map(fn($faq) => ['faq' => $faq, 'locale' => $locale])),
        ], 200);
    }
}
