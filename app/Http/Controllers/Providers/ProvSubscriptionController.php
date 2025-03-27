<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use App\Models\Subscription;
use App\Models\UsersAccount;

// Requests
use App\Http\Requests\Providers\ProvSubscriptionRequest;

// Responses
use App\Http\Resources\Providers\ProvSubscriptionResource;
use App\Http\Resources\Providers\ProvSubUpgradeResource;



class ProvSubscriptionController extends Controller
{
    // Show all active available sub
    public function showAvailableSubscriptions()
    {
        // ✅ الحصول على المستخدم المسجل حاليًا (بروفايدر)
        $provider = UsersAccount::find(auth()->id());

        // ✅ التأكد من أن المستخدم هو بروفايدر فقط
        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only providers can show subscriptions.',
                'data' => null,
            ], 403);
        }
        $subscriptions = Subscription::where('is_active', 1)->with('translations')->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No available subscriptions at the moment.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Available subscriptions retrieved successfully',
            'data' => ProvSubscriptionResource::collection($subscriptions),
        ]);
    }

    // Show subscriptions with my sub
    public function showUpgradeSubscriptions()
    {
        $provider = UsersAccount::find(auth()->id());

        if (!$provider || $provider->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only providers can show subscriptions.',
                'data' => null,
            ], 403);
        }

        // ✅ جلب الباقة الحالية للمستخدم
        $currentSubscription = Subscription::find($provider->subscription_id);

        // ✅ جلب باقي الباقات المتاحة باستثناء الحالية
        $otherSubscriptions = Subscription::where('is_active', 1)
            ->where('id', '!=', $provider->subscription_id)
            ->with('translations')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Upgrade subscriptions retrieved successfully',
            'data' => [
                'current_subscription' => $currentSubscription ? new ProvSubUpgradeResource($currentSubscription) : null,
                'other_subscriptions' => ProvSubUpgradeResource::collection($otherSubscriptions),
            ]
        ]);
    }

    // Show one subscription
    public function showOneSubscription($id)
    {
        // ✅ الحصول على المستخدم المسجل حاليًا (بروفايدر)
        $provider = UsersAccount::find(auth()->id());

        // ✅ التأكد من أن المستخدم هو بروفايدر فقط
        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only providers can see subscriptions.',
                'data' => null,
            ], 403);
        }

        // ✅ التحقق مما إذا كان البروفايدر يمتلك هذا الاشتراك
        if ($provider->subscription_id != $id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You do not have permission to view this subscription or not found.',
                'data' => null,
            ], 403);
        }

        // ✅ جلب الباقة بناءً على الـ ID
        $subscription = Subscription::find($id);
        // ✅ التحقق من وجود الباقة
        // if (!$subscription) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Subscription not found.',
        //         'data' => null,
        //     ], 404);
        // }
        // $subscription = Subscription::find($provider->subscription_id);

        // ✅ جلب تاريخ انتهاء الاشتراك من جدول users_accounts
        $subscriptionExpiresAt = UsersAccount::where('subscription_id', $id)->value('subscription_expires_at');

        if ($provider->subscription_expires_at && now()->greaterThan($provider->subscription_expires_at)) {
            $provider->update([
                'subscription_id' => null,
                'subscription_expires_at' => null,
            ]);
        }
        // ✅ التحقق مما إذا كان الاشتراك منتهيًا أو غير موجود
        if (is_null($subscriptionExpiresAt)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your subscription has expired. Please subscribe to a plan.',
                'data' => null,
            ], 400);
        }

        // if (now()->greaterThan($subscriptionExpiresAt)) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Your subscription has expired. Please subscribe to a new plan.',
        //         'data' => null,
        //     ], 400);
        // }
        return response()->json([
            'status' => 'success',
            'message' => 'Subscription details retrieved successfully',
            'data' => [
                'subscription' => new ProvSubUpgradeResource($subscription),
                'subscription_expires_at' => $subscriptionExpiresAt,
            ],

        ]);
    }
    // Select one to subscribe
    public function subscribeToPlan(ProvSubscriptionRequest $request)
    {
        // ✅ الحصول على المستخدم المسجل حاليًا (بروفايدر)
        $provider = UsersAccount::find(auth()->id());

        /////////////////////////////////////////////////////////////////////
        // ✅ التحقق مما إذا كان الاشتراك قد انتهى
        if ($provider->subscription_expires_at && now()->greaterThan($provider->subscription_expires_at)) {
            $provider->update([
                'subscription_id' => null,
                'subscription_expires_at' => null,
            ]);
        }

        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'Your subscription has expired. Please subscribe to a new plan.',
        //         'data' => null,
        //     ], 400);
        // }
        /////////////////////////////////////////////////////////////////////
        // ✅ التأكد من أن المستخدم هو بروفايدر فقط
        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only providers can make subscriptions.',
                'data' => null,
            ], 403);
        }
        // ✅ التأكد من عدم وجود اشتراك نشط
        if ($provider->subscription_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are already subscribed to a plan. Cancel your current plan or wait for it to expire.',
                'data' => null,
            ], 400);
        }

        $subscription = Subscription::with('translations')->find($request->subscription_id);

        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription plan not found.',
                'data' => null,
            ], 404);
        }

        // استرجاع المدة بالطريقة الصحيحة
        $duration = $subscription->translations->first()->duration ?? 'Unknown duration';
        // حساب تاريخ انتهاء الصلاحية
        $expirationDate = now()->addDays(intval($duration))->format('Y-m-d H:i:s');
        // تحديث بيانات البروفايدر
        $provider->update([
            'subscription_id' => $request->subscription_id,
            'subscription_expires_at' => $expirationDate,
        ]);

        // **إرسال إشعار للأدمن**
        $admins = UsersAccount::where('role', 'superAdmin')->first();
        $planTitle = $subscription->translations->where('locale', $request->header('Accept-Language'))->first()->title ?? 'Unknown Plan';

        $admins->notify(new GeneralNotification(
            'New Subscription',
            "provider {$provider->username} has subscribed to {$planTitle}. Expiry at: {$expirationDate}."
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription successful!',
            'data' => new ProvSubscriptionResource($subscription),
        ], 200);
    }

    // upgrade the subscription 
    public function upgradeSubscription(ProvSubscriptionRequest $request)
    {
        // ✅ الحصول على المستخدم المسجل حاليًا (بروفايدر)
        $provider = UsersAccount::find(auth()->id());

        /////////////////////////////////////////////////////////////////////
        // // ✅ التحقق مما إذا كان الاشتراك قد انتهى
        if ($provider->subscription_expires_at && now()->greaterThan($provider->subscription_expires_at)) {
            $provider->update([
                'subscription_id' => null,
                'subscription_expires_at' => null,
            ]);


            return response()->json([
                'status' => 'error',
                'message' => 'Your subscription has expired. Please subscribe to a new plan.',
                'data' => null,
            ], 400);
        }
        /////////////////////////////////////////////////////////////////////

        // ✅ التأكد من أن المستخدم هو بروفايدر فقط
        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only providers can upgrade subscriptions.',
                'data' => null,
            ], 403);
        }

        // ✅ التأكد من أن البروفايدر لديه اشتراك بالفعل
        if (!$provider->subscription_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not subscribed to any plan yet.',
                'data' => null,
            ], 400);
        }

        // ✅ التأكد من أنه لا يحاول الاشتراك في نفس الباقة
        if ($provider->subscription_id == $request->subscription_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are already subscribed to this plan.',
                'data' => null,
            ], 400);
        }

        // ✅ جلب الاشتراك الجديد مع الترجمات
        $newSubscription = Subscription::with('translations')->find($request->subscription_id);
        if (!$newSubscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription plan not found.',
                'data' => null,
            ], 404);
        }

        // ✅ استرجاع مدة الاشتراك الجديد
        $duration = $newSubscription->translations->first()->duration ?? null;
        // dd($duration);

        // ✅ حساب تاريخ انتهاء الاشتراك الجديد بناءً على مدته
        $expirationDate = now()->addDays(intval($duration))->format('Y-m-d H:i:s');

        // ✅ تحديث اشتراك البروفايدر
        $provider->update([
            'subscription_id' => $request->subscription_id,
            'subscription_expires_at' => $expirationDate, // تحديث تاريخ الانتهاء
        ]);

        $admins = UsersAccount::where('role', 'superAdmin')->first();
        $planTitle = $newSubscription->translations->first()->title ?? 'Unknown Plan';

        $admins->notify(new GeneralNotification(
            'upgrading Subscription',
            "provider {$provider->username} has upgraded to {$planTitle}. Expiry at: {$expirationDate}."
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription upgraded successfully!',
            'data' => new ProvSubscriptionResource($newSubscription),
        ], 200);
    }

    // Cancele subscription
    public function cancelSubscription(ProvSubscriptionRequest $request)
    {
        // ✅ جلب البروفايدر المسجل حاليًا
        $provider = UsersAccount::find(auth()->id());

        // ✅ التأكد من أن المستخدم هو بروفايدر فقط
        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only providers can cancel subscriptions.',
                'data' => null,
            ], 403);
        }

        // ✅ التأكد من أن البروفايدر لديه اشتراك بالفعل
        if (!$provider->subscription_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not subscribed to any plan.',
                'data' => null,
            ], 400);
        }

        // ✅ التحقق من أن subscription_id المطلوب مطابق للاشتراك الحالي
        if ($request->has('subscription_id') && $request->subscription_id != $provider->subscription_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only cancel your current subscription.',
                'data' => null,
            ], 400);
        }

        // ✅ إلغاء الاشتراك
        $provider->update([
            'subscription_id' => null,
            'subscription_expires_at' => null, // تصفير تاريخ الانتهاء أيضًا
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your subscription has been canceled successfully.',
            'data' => null,
        ], 200);
    }
}
