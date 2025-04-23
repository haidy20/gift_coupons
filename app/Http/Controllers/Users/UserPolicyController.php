<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Policy;

// Requests
use App\Http\Requests\Users\UserShowPolicyRequest;

// Responses
use App\Http\Resources\Users\UserShowPolicyResource;

class UserPolicyController extends Controller
{
    public function show($id, UserShowPolicyRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $policy = Policy::with('translations')->find($id);

        if (!$policy) {
            return response()->json([
                'status' => 'fail',
                'message' => 'policy not found',
                'data' => null
            ], 404);
        }

        $translation = $policy->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Translation not found',
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'policy retrived successfully',
            'data' => new UserShowPolicyResource($translation),
        ], 201);
    }
}
