<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TermTranslation;
use App\Models\Term;


// Requests
use App\Http\Requests\Providers\ProvShowTermRequest;
// Resources
use App\Http\Resources\Providers\ProvShowTermsResource;

class ProvTermController extends Controller
{
    public function show($id, ProvShowTermRequest $request)
    {
        $locale = $request->header('Accept-Language', 'en'); // الافتراضي English
        $term = Term::with('translations')->find($id);

        if (!$term) {
            return response()->json([
                'status' => "fail",
                'message' => trans('messages.term_not_found'),
                'data' => null

            ], 404);
        }

        $translation = $term->translation($locale);

        if (!$translation) {
            return response()->json([
                'status' => "fail",
                'message' => trans('messages.translation_not_found'),
                'data' => null
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.term_retrieved_successfully'),
            'data' => new ProvShowTermsResource($translation),
        ], 201);
    }
}
