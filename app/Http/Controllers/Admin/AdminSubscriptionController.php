<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\SubscriptionTranslation;
// Requests
use App\Http\Requests\Admin\AdminSubscriptionRequest;
// Resources
use App\Http\Resources\Admin\AdminSubscriptionResource;


class AdminSubscriptionController extends Controller
{
    public function index()
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }

        $subscriptions = Subscription::with('translations')->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.subscriptions_retrieved_successfully'),
            'data' => AdminSubscriptionResource::collection($subscriptions),
        ]);
    }

    public function show($subscription)
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }

        $subscription = Subscription::with('translations')->find($subscription);

        if (!$subscription) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.subscription_not_found'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.subscription_retrieved_successfully'),
            'data' => new AdminSubscriptionResource($subscription),
        ]);
    }

    public function create(AdminSubscriptionRequest $request)
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }

        $subscription = Subscription::create($request->only(['is_active']));

        foreach ($request->translations as $translation) {
            $subscription->translations()->create($translation);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.subscription_created_successfully'),
            'data' => new AdminSubscriptionResource($subscription),
        ]);
    }

    public function update(AdminSubscriptionRequest $request, Subscription $subscription)
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }

        // ✅ تأكد أن الاشتراك موجود
        if (!$subscription) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.subscription_not_found'),
                
                'data' => null,
            ], 404);
        }

        $subscription->update($request->only(['is_active']));

        foreach ($request->translations as $translation) {
            SubscriptionTranslation::updateOrCreate(
                ['subscription_id' => $subscription->id, 'locale' => $translation['locale']],
                $translation
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.subscription_updated_successfully'),
            'data' => new AdminSubscriptionResource($subscription),
        ]);
    }

    public function destroy(Subscription $subscription)
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null,
            ], 403);
        }

        $subscription->delete();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.subscription_deleted_successfully'),
            'data' => null,
        ]);
    }
}
