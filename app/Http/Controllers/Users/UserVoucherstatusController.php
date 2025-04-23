<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;

// Requests
use App\Http\Requests\Users\UserGetVouchersStatusRequest;
// Resources
use App\Http\Resources\Users\UserGetVouchersStatusResource;

class UserVoucherstatusController extends Controller
{
    //User get status of vouchers of all providers
    public function getUserVouchers(UserGetVouchersStatusRequest $request)
    {
        if (auth()->user()->role !== 'client') {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized. only clients can see all roles',
                'data' => null
            ], 403);
        }

        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized access. Please log in.',
                'data' => null
            ], 401);
        }
        $status = $request->query('status', '');

        if (!in_array($status, ['active', 'used', 'expired'])) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid status provided. Allowed values: active, used, expired.',
                'data' => null
            ], 400);
        }


        $vouchers = Voucher::whereHas('users', function ($query) use ($user, $status) {
            $query->where('user_id', $user->id)
                ->where('user_vouchers.status', $status);
        })
            ->with(['provider', 'users' => function ($query) use ($user, $status) {
                $query->where('user_id', $user->id)
                    ->where('user_vouchers.status', $status);
            }])
            ->get();

        if ($vouchers->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No vouchers found for the given status.',
                'data' => [],
            ], 200);
        }

        // حل المشكلة: نمرر كلاً من الفاوتشر واليوزر للريسورس 
        $formattedVouchers = $vouchers->flatMap(function ($voucher) {
            return $voucher->users->map(function ($user) use ($voucher) {
                return new UserGetVouchersStatusResource((object) [
                    'voucher' => $voucher,
                    'pivot' => $user->pivot,
                ]);
            });
        });

        return response()->json([
            // 'vouchers_count' => $formattedVouchers->count(),
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => $formattedVouchers,
        ]);
    }

    //User get details of one voucher of specific status -> all providers -> generally
    public function getUserVoucherById($purchaseId)
    {

        if (auth()->user()->role !== 'client') {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized. only clients can see all roles',
                'data' => null
            ], 403);
        }

        $user = auth('api')->user();

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized access. Please log in.',
                'data' => null
            ], 401);
        }

        // البحث عن الفاوتشر بناءً على الـ purchaseId الموجود في جدول user_vouchers
        $voucher = Voucher::whereHas('users', function ($query) use ($user, $purchaseId) {
            $query->where('user_id', $user->id)
                ->where('user_vouchers.id', $purchaseId);
        })
            ->with(['provider', 'users' => function ($query) use ($user, $purchaseId) {
                $query->where('user_id', $user->id)
                    ->where('user_vouchers.id', $purchaseId);
            }])
            ->first();


            if (!$voucher) {
                return response()->json([
                    'status' => 'fail',
                    'message' => 'Voucher not found or not purchased to this user.',
                    'data' => null
                ], 404);
            }
    

        // تجهيز البيانات بنفس التنسيق
        $formattedVoucher = $voucher->users->map(function ($user) use ($voucher) {
            return new UserGetVouchersStatusResource((object) [
                'voucher' => $voucher,
                'pivot' => $user->pivot,
            ]);
        })->first(); // لأننا نحتاج فقط للنسخة المحددة

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher retrieved successfully',
            'data' => $formattedVoucher,
        ]);
    }
}
