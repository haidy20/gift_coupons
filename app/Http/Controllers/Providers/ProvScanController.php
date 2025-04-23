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
                'status' => 'fail',
                'message' => trans('messages.invalid_qr_code_id'),
                'data' => null
            ], 404);
        }

        if ($provider->id !== $qrCode->provider_id) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_qr_scan'),
                'data' => null
            ], 403);
        }

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
                'status' => 'fail',
                'message' => trans('messages.voucher_already_scanned_or_expired'),
                'data' => null
            ], 404);
        }

        // التحقق من انتهاء صلاحية الفاوتشر المحدد
        if (strtotime($userVoucher->expiry_date) < time()) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.voucher_expired'),
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
        // $provider->notify(new GeneralNotification(
        //     "New Voucher Scanned",
        //     "User {$qrCode->user->username} has successfully activated the voucher ({$qrCode->voucher->name})."
        // ));

        // // Notification to user
        // $qrCode->user->notify(new GeneralNotification(
        //     "Voucher Used For User",
        //     "Your voucher ({$qrCode->voucher->name}) has been successfully scanned and activated from provider {$qrCode->provider->username}."
        // ));

        $provider->notify(new GeneralNotification(
            trans('messages.new_voucher_scanned_title'),
            trans('messages.new_voucher_scanned_body', [
                'username' => $qrCode->user->username,
                'voucher' => $qrCode->voucher->name
            ])
        ));
        
        $qrCode->user->notify(new GeneralNotification(
            trans('messages.voucher_used_for_user_title'),
            trans('messages.voucher_used_for_user_body', [
                'voucher' => $qrCode->voucher->name,
                'provider' => $qrCode->provider->username
            ])
        ));
        

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.qr_code_scanned_successfully'),
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
            'message' => trans('messages.scanned_users_list_retrieved'),
            'data' => ProvScannedUsersResource::collection($scannedUsers)
        ]);
    }
}
