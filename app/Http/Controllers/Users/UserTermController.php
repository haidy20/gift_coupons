<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Term;

// Requests
use App\Http\Requests\Users\UserShowTermRequest;

// Responses
use App\Http\Resources\Users\UserShowTermResource;

class UserTermController extends Controller
{
    public function show($id, UserShowTermRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $term = Term::with('translations')->find($id);

        if (!$term) {
            return response()->json(['message' => 'Term not found'], 404);
        }
        $translation = $term->translation($locale);

        if (!$translation) {
            return response()->json(['message' => 'Translation not found'], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Term retrived successfully',
            'data' => new UserShowTermResource($translation),
        ], 201);
    }
}
