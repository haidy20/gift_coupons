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
    // public function getUserVouchers(UserGetVouchersStatusRequest $request)
    // {
    //     $user = auth()->user();

    //     if (!$user) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $status = $request->query('status', '');

    //     if (!in_array($status, ['active', 'used', 'expired'])) {
    //         return response()->json(['error' => 'Invalid status.'], 400);
    //     }

    //     // جلب الفاوتشرات الخاصة بالمستخدم بناءً على جدول `vouchers`
    //     $vouchers = Voucher::whereHas('users', function ($query) use ($user, $status) {
    //         $query->where('user_id', $user->id)
    //             ->where('user_vouchers.status', $status);
    //     })
    //         ->with(['provider', 'users' => function ($query) use ($user, $status) {
    //             $query->where('user_id', $user->id)
    //                 ->where('user_vouchers.status', $status);
    //         }])
    //         ->get()

    //         ->flatMap(function ($voucher) use ($status) {
    //             return $voucher->users->map(function ($user) use ($voucher, $status) {
    //                 $pivot = $user->pivot;

    //                 return [
    //                     'provider_name' => $voucher->provider->username ?? 'Unknown',
    //                     'latitude' => $voucher->provider->latitude ?? null,
    //                     'longitude' => $voucher->provider->longitude ?? null,
    //                     'location' => $voucher->provider->location ?? null,
    //                     'id' => $voucher->id,
    //                     'amount' => $voucher->amount,
    //                     'random_num' => $voucher->random_num,
    //                     'description' => $voucher->description,
    //                     'purchase_date' => match ($status) {
    //                         'active' => $pivot->purchase_date,
    //                         'used' => $pivot->used_date,
    //                         'expired' => 'Voucher expired',
    //                     },
    //                 ];
    //             });
    //         });
    //         return response()->json([
    //             'vouchers_count' => $vouchers->count(),
    //             'status'=>'success',
    //             'message'=>'Vouchers retrived successfully ',
    //             'data' => $vouchers,
    //         ]);
    // }


    public function getUserVouchers(UserGetVouchersStatusRequest $request)
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    
        $status = $request->query('status', '');
    
        if (!in_array($status, ['active', 'used', 'expired'])) {
            return response()->json(['error' => 'Invalid status.'], 400);
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
            'vouchers_count' => $formattedVouchers->count(),
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => $formattedVouchers,
        ]);
    }
    
    //User get details of one voucher of specific status all providers
    // public function getVoucherDetails($id, UserGetVouchersStatusRequest $request)
    // {
    //     $user = auth()->user();
    
    //     if (!$user) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }
    
    //     $status = $request->query('status', '');
    
    //     if (!in_array($status, ['active', 'used', 'expired'])) {
    //         return response()->json(['error' => 'Invalid status.'], 400);
    //     }
    
    //     // البحث عن الفاوتشر بناءً على الـ ID والحالة المطلوبة
    //     $voucher = Voucher::whereHas('users', function ($query) use ($user, $id, $status) {
    //             $query->where('user_id', $user->id)
    //                 ->where('user_vouchers.voucher_id', $id)
    //                 ->where('user_vouchers.status', $status);
    //         })
    //         ->with(['provider', 'users' => function ($query) use ($user, $id, $status) {
    //             $query->where('user_id', $user->id)
    //                 ->where('user_vouchers.voucher_id', $id)
    //                 ->where('user_vouchers.status', $status);
    //         }])
    //         ->first();
    
    //     if (!$voucher) {
    //         return response()->json(['error' => 'Voucher not found.'], 404);
    //     }
    
    //     // استخراج بيانات الفاوتشر
    //     $voucherDetails = $voucher->users->map(function ($user) use ($voucher) {
    //         return new UserGetVouchersStatusResource((object) [
    //             'voucher' => $voucher,
    //             'pivot' => $user->pivot,
    //         ]);
    //     })->first(); // لأننا نريد عنصر واحد فقط
    
    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Voucher details retrieved successfully',
    //         'data' => $voucherDetails,
    //     ]);
    // }

    
    
}
