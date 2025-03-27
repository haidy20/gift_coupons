<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\QrCode;
use App\Models\ScannedUser;
use App\Models\UsersAccount;


use Illuminate\Support\Facades\DB;
use App\Notifications\GeneralNotification;


// Resources
use App\Http\Resources\Providers\ProvScanQrCodeResource;
use App\Http\Resources\Providers\ProvScannedUsersResource;

class ProvScanController extends Controller
{
    public function scanQrCode($id)
    {
        $provider = auth('api')->user();
        $qrCode = QrCode::with(['user', 'voucher'])->find($id);

        if (!$qrCode) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid QR Code id.',
                'data' => null
            ], 404);
        }

        if ($provider->id !== $qrCode->provider_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to scan this QR code.',
                'data' => null
            ], 403);
        }


        // **التحقق مما إذا كان الفاوتشر قد تم تفعيله مسبقًا لهذا المستخدم**
        // $isScanned = DB::table('scanned_users')
        //     ->where('user_id', $qrCode->user_id)
        //     ->where('voucher_id', $qrCode->voucher_id)
        //     ->exists();

        // if ($isScanned) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'This QR Code has already been activated before.',
        //         'data' => null
        //     ], 400);
        // }

        // البحث عن الفاوتشر الخاص بالمستخدم والذي لم يتم استخدامه
        // $userVoucher = DB::table('user_vouchers')
        //     ->where('user_id', $qrCode->user_id)
        //     ->where('voucher_id', $qrCode->voucher_id)
        //     ->whereNull('used_date')
        //     ->first();

        $userVoucher = DB::table('user_vouchers')
            ->where([
                ['user_id', '=', $qrCode->user_id],
                ['voucher_id', '=', $qrCode->voucher_id],
                ['used_date', '=', null],
                ['expiry_date', '>=', now()]
            ])
            ->first();


        // تحديث الفاوتشرات المنتهية قبل التحقق
        DB::table('user_vouchers')
            ->where('user_id', $qrCode->user_id)
            ->where('voucher_id', $qrCode->voucher_id)
            ->whereNull('used_date')
            ->where('expiry_date', '<', now())
            ->update([
                'status' => 'expired',
                'updated_at' => now(),
            ]);

        if (!$userVoucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher has been scanned for this user or expired.',
                'data' => null
            ], 404);
        }

        // التحقق من انتهاء صلاحية الفاوتشر المحدد
        if (strtotime($userVoucher->expiry_date) < time()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This voucher has expired.',
                'data' => [
                    'expiry_date' => $userVoucher->expiry_date
                ]
            ], 400);
        }

        // تحديث تاريخ الاستخدام وحالة الفاوتشر
        DB::table('user_vouchers')
            ->where('id', $userVoucher->id)
            ->update([
                'used_date' => now(),
                'status' => 'used',
                'updated_at' => now(),
            ]);

        // 🆕 إدراج البيانات في جدول scanned_users
        DB::table('scanned_users')->insert([
            'user_id' => $qrCode->user_id,
            'provider_id' => $provider->id,
            'voucher_id' => $qrCode->voucher_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Notification to provider
        $provider = UsersAccount::find($userVoucher->provider_id);
        $provider->notify(new GeneralNotification(
            "New Voucher Scanned",
            "User {$qrCode->user->username} has successfully activated the voucher ({$qrCode->voucher->name})."
        ));

        // Notification to user
        $qrCode->user->notify(new GeneralNotification(
            "Voucher Used For User",
            "Your voucher ({$qrCode->voucher->name}) has been successfully scanned and activated from provider {$qrCode->provider->username}."
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'User QR code scanned successfully.',
            'data' => new ProvScanQrCodeResource($qrCode)
        ]);
    }


    // Get all scanned users by theire provider who makes scanning
    public function getScannedUsersByProvider()
    {
        $provider = auth('api')->user(); // Get the authenticated provider

        // Retrieve scanned users by provider
        $scannedUsers = ScannedUser::with(['user:id,username,phone,countries_id', 'voucher:id,random_num,amount'])
            ->where('provider_id', $provider->id) // Filter by provider ID
            ->latest() // بدلاً من orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'List of scanned users retrieved successfully.',
            'data' => ProvScannedUsersResource::collection($scannedUsers)
        ]);
    }
}
