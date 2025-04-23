<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checkout;
use App\Models\OrderDetail;
use App\Models\Cart;
use Carbon\Carbon;
use App\Models\QrCode as QrCodeModel;
use App\Notifications\GeneralNotification;
use App\Models\UsersAccount;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Jobs\NotifyUserVoucherExpiry; // ✅ أضف هذا السطر



// Responses
use App\Http\Resources\Users\UserCheckoutResource;
use App\Http\Resources\Users\UserAddToCartResource;


class UserCheckoutController extends Controller
{
    // Three functions for (checkouts) for clean code 
    public function checkoutOrder()
    {
        $user = auth()->user();
        $cart = $user->cart;

        if (!$cart || $cart->vouchers->isEmpty()) {
            return response()->json([
                'status'=>'fail',
                'message' => 'Your cart is empty. Add vouchers before checkout.',
                'data'=>null
            ], 400);
        }

        return DB::transaction(fn() => $this->processCheckout($user, $cart));
    }

    private function processCheckout($user, $cart)
    {
        $checkout = Checkout::create(['user_id' => $user->id]);

        $orderDetails = $cart->vouchers->map(function ($voucher) use ($checkout, $user) {
            return $this->qrcodeVoucher($voucher, $checkout, $user);
        });

        $totalQuantity = $orderDetails->sum('quantity');
        $totalVouchers = $orderDetails->sum('total_price');

        $checkout->update([
            'total_quantity' => $totalQuantity,
            'total_for_all' => $totalVouchers,
        ]);

        // **إضافة بيانات القسائم إلى جدول `user_vouchers`**
        foreach ($cart->vouchers as $voucher) {
            $expiryDate = now()->addDays(intval($voucher->duration_days))->format('Y-m-d H:i:s');

            DB::table('user_vouchers')->insert([
                'user_id' => $user->id,
                'voucher_id' => $voucher->id,
                'provider_id' => $voucher->provider_id,
                'purchase_date' => now(),
                'expiry_date' => $expiryDate,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // تحديد وقت الإشعار قبل انتهاء الصلاحية دقيقة مثلاً
            $notifyAt = now()->addMinutes(1);
            NotifyUserVoucherExpiry::dispatch($user, $voucher, $expiryDate)->delay($notifyAt);
        
            $transactionAmount = $voucher->amount * ($voucher->pivot->quantity ?? 1);

            // ✅ إرسال إشعار للبروفايدر
            $provider = UsersAccount::find($voucher->provider_id);
            if ($provider) {
                $provider->notify(new GeneralNotification(
                    'New Voucher Purchased',
                    "User {$user->username} has successfully purchased a voucher ({$voucher->name}). Expiry Date: {$expiryDate}."
                ));
            }

            // ✅ **إضافة معاملة الإيداع في transactions**
            DB::table('transactions')->insert([
                'transaction_code' => strtoupper(uniqid('TXN_')), // توليد كود فريد للمعاملة
                'transaction_type' => 'deposit',
                'provider_id' => $voucher->provider_id,
                'checkout_id' => $checkout->id,
                'amount' => $transactionAmount, // المبلغ المدفوع للمزود
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // تحديث محفظة المزود
            DB::table('wallets')->updateOrInsert(
                ['provider_id' => $voucher->provider_id],
                [
                    'balance' => DB::raw("balance + $transactionAmount"),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }


        // مسح السلة بعد الشراء
        $cart->vouchers()->detach();
        $cart->delete();

        return response()->json([
            'status' => 'success',
            'message' => "Order placed successfully with $totalQuantity items totaling $$totalVouchers.",
            'order_id' => $checkout->id,
            'user_id' => $user->id,
            'total_quantity' => $totalQuantity,
            'total_for_all' => $totalVouchers,
            'order_details' => $orderDetails,
        ], 200);

        return response()->json([
            'status' => 'success',
            'message' => "Order placed successfully with $totalQuantity items totaling $$totalVouchers.",
            'data' => new UserCheckoutResource($checkout)
        ], 200);
    }


    private function qrcodeVoucher($voucher, $checkout)
    {
        Log::info('Voucher Data:', ['voucher' => $voucher]);

        $quantity = $voucher->pivot->quantity ?? 1;
        $totalPrice = $voucher->amount * $quantity;

        // إدخال بيانات QR Code واسترجاع ID الخاص به
        $qrCodeId = DB::table('qr_codes')->insertGetId([
            'voucher_id' => $voucher->id,
            'user_id' => $checkout->user_id,
            'provider_id' => $voucher->provider_id, // ✅ إضافة provider_id
            'qr_code_path' => '', // سيتم تحديثه لاحقًا بعد حفظ الـ QR
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $qrCodePath = "qrcodes/voucher_{$voucher->id}_" . time() . rand(1000, 9999) . ".svg";
        $qrCodeSvg = QrCode::format('svg')->size(300)->generate($qrCodeId);


        Storage::disk('public')->put($qrCodePath, $qrCodeSvg);
        // تحديث مسار الـ QR Code بعد إنشائه
        DB::table('qr_codes')->where('id', $qrCodeId)->update([
            'qr_code_path' => $qrCodePath,
            'updated_at' => now(),
        ]);

        return [
            'voucher_id' => $voucher->id,
            'voucher_name' => $voucher->name,
            'quantity' => $quantity,
            'amount' => $voucher->amount,
            'total_price' => $totalPrice,
            'qr_code_url' => asset('storage/' . $qrCodePath),
        ];
    }
}
