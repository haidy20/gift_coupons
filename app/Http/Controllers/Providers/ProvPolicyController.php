<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Policy;

// Requests
use App\Http\Requests\Providers\ProvShowPolicyRequest;

// Responses
use App\Http\Resources\Providers\ProvShowPolicyResource;

class ProvPolicyController extends Controller
{
    public function show($id, ProvShowPolicyRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $policy = Policy::with('translations')->find($id);

        if (!$policy) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.policy_not_found'),
                'data' => null
            ], 404);
        }

        $translation = $policy->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.translation_not_found'),
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.policy_retrieved_successfully'),
            'data' => new ProvShowPolicyResource($translation),
        ], 201);
    }
}
