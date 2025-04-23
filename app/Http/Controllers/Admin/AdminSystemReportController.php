<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use App\Models\UsersAccount;
// Requests
// use App\Http\Requests\Admin\AdminSubscriptionRequest;
// Resources
use App\Http\Resources\Admin\AdminAllStatusVouchersResource;
use App\Http\Resources\Admin\AdminGetAllPersonsResource;
use App\Http\Resources\Admin\AdminGetCountsResource;



class AdminSystemReportController extends Controller
{
    /**
     * Get all vouchers of providers.
     */

    public function AdminGetAllVouchers()
    {
        // التأكد من أن المستخدم هو سوبر أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        // جلب جميع الفاوتشرات فقط بدون فلترة
        $vouchers = Voucher::with('provider')->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.vouchers_retrieved_successfully'),
            'data' => AdminAllStatusVouchersResource::collection($vouchers)
        ]);

        /**
         * ✅ دالة لجلب حالات الفاوتشرات: purchased, expired, used
         */
    }

    /**
     * Get all status of vouchers and thiere count.
     */

    public function AdminGetAllStatusVouchers(Request $request)
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        // التحقق من وجود `status` في الطلب
        if (!$request->has('status')) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.status_parameter_required'),
                'data' => null
            ], 400);
        }

        $status = $request->query('status');

        // جلب الفاوتشرات الخاصة بهذه الحالة فقط
        $vouchers = Voucher::with(['provider', 'users'])
            ->whereHas('users', function ($query) use ($status) {
                $query->where('user_vouchers.status', $status);
            })
            ->get();

        // حساب العدد الخاص بهذه الحالة فقط
        $count = DB::table('user_vouchers')
            ->where('status', $status)
            ->count();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.vouchers_with_status_retrieved', ['status' => $status]),
            'data' => [
                'status' => $status,
                'count' => $count, // العدد الخاص بالحالة المطلوبة فقط
                'vouchers' => AdminAllStatusVouchersResource::collection($vouchers),
            ],
        ]);
    }

    /**
     * Get all users by role and count each type of them.
     */
    public function getAllUsersByRole()
    {
        // التأكد من أن المستخدم هو سوبر أدمن
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }
        // إحضار المستخدمين حسب الدور
        $admins = UsersAccount::where('role', 'admin')->get();
        $providers = UsersAccount::where('role', 'provider')->get();
        $clients = UsersAccount::where('role', 'client')->get();

        // تحضير الاستجابة
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.users_retrieved_successfully'),
            'data' => [
                'counts' => [
                    'admins' => $admins->count(),
                    'providers' => $providers->count(),
                    'clients' => $clients->count(),
                ],
                'users' => [
                    'admins' => AdminGetAllPersonsResource::collection($admins),
                    'providers' => AdminGetAllPersonsResource::collection($providers),
                    'clients' => AdminGetAllPersonsResource::collection($clients),
                ],
            ],
        ], 200);
    }

    /**
     * Get all users by role and count each type of them.
     */
    public function getCounts()
    {
        if (auth()->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $counts = [
            'contacts' => DB::table('contacts')->count(),
            'countries' => DB::table('countries')->count(),
            'faqs' => DB::table('faqs')->count(),
            'faq_translations' => DB::table('faq_translations')->count(),
            'categories' => DB::table('categories')->count(),
            'vouchers' => DB::table('vouchers')->count(),
            'subscriptions' => DB::table('subscriptions')->count(),
            'subscription_translations' => DB::table('subscription_translations')->count(),
            'feedbacks' => DB::table('feedbacks')->count(),
        ];

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.counts_retrieved_successfully'),
            'data' => new AdminGetCountsResource($counts)
        ]);
    }
}
