<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checkout;
use App\Models\OrderDetail;
use App\Models\Cart;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;


class UserCheckoutController extends Controller
{
    // public function checkoutOrder()
    // {
    //     $user = auth()->user();
    //     $cart = $user->cart;

    //     if (!$cart || $cart->vouchers->isEmpty()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Your cart is empty. Add vouchers before checkout.',
    //         ], 400);
    //     }

    //     return DB::transaction(function () use ($user, $cart) {
    //         $checkout = Checkout::create(['user_id' => $user->id]);

    //         $orderDetails = $cart->vouchers->map(function ($voucher) use ($checkout) {
    //             $quantity = $voucher->pivot->quantity ?? 1;
    //             $totalPrice = $voucher->amount * $quantity;

    //             ::create([
    //                 'order_id' => $checkout->id,
    //                 'voucher_id' => $voucher->id,
    //             ]);

    //             return [
    //                 'voucher_id' => $voucher->id,
    //                 'voucher_name' => $voucher->name,
    //                 'quantity' => $quantity,
    //                 'amount' => $voucher->amount,
    //                 'total_price' => $totalPrice,
    //             ];
    //         });

    //         $totalQuantity = $orderDetails->sum('quantity');
    //         $totalVouchers = $orderDetails->sum('total_price');

    //         $checkout->update([
    //             'total_quantity' => $totalQuantity,
    //             'total_for_all' => $totalVouchers,
    //         ]);

    //         $cart->vouchers()->detach();
    //         $cart->delete();

    //         return response()->json([
    //             'status' => 'success',
    //             'message' => "Order placed successfully with $totalQuantity items totaling $$totalVouchers.",
    //             'order_id' => $checkout->id,
    //             'user_id' => $user->id,
    //             'total_quantity' => $totalQuantity,
    //             'total_for_all' => $totalVouchers,
    //             'order_details' => $orderDetails,
    //         ], 200);

    //     });
    // }
    
    public function checkoutOrder()
    {
        $user = auth()->user();
        $cart = $user->cart;
    
        if (!$cart || $cart->vouchers->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your cart is empty. Add vouchers before checkout.',
            ], 400);
        }
    
        return DB::transaction(function () use ($user, $cart) {
            $checkout = Checkout::create(['user_id' => $user->id]);
    
            $orderDetails = $cart->vouchers->map(function ($voucher) use ($checkout) {
                $quantity = $voucher->pivot->quantity ?? 1;
                $totalPrice = $voucher->amount * $quantity;
    
                // حفظ تفاصيل الطلب
                OrderDetail::create([
                    'order_id' => $checkout->id,
                    'voucher_id' => $voucher->id,
                ]);
    
                // إنشاء كود QR وتحسينه
                $qrCodePath = $this->generateQrCode($voucher);
    
                return [
                    'voucher_id' => $voucher->id,
                    'voucher_name' => $voucher->name,
                    'quantity' => $quantity,
                    'amount' => $voucher->amount,
                    'total_price' => $totalPrice,
                    'qr_code_url' => asset('storage/' . $qrCodePath), // إرجاع رابط الصورة
                ];
            });
    
            $totalQuantity = $orderDetails->sum('quantity');
            $totalVouchers = $orderDetails->sum('total_price');
    
            $checkout->update([
                'total_quantity' => $totalQuantity,
                'total_for_all' => $totalVouchers,
            ]);
    
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
        });
    }
    
    
    private function generateQrCode($voucher)
    {
        $qrCodePath = 'qrcodes/' . $voucher->id . '.png';
    
        // إنشاء كود QR باستخدام simple-qrcode
        $qrCodeImage = QrCode::format('png')->size(300)->generate($voucher->id);
    
        // تحسين الصورة باستخدام Intervention Image
        $image = Image::make($qrCodeImage)
            ->resize(300, 300)
            ->encode('png', 100);
    
        // حفظ الصورة
        Storage::disk('public')->put($qrCodePath, $image);
    
        // حفظ بيانات QR في قاعدة البيانات
        QrCode::create([
            'voucher_id' => $voucher->id,
            'qr_code_path' => $qrCodePath,
        ]);
    
        return $qrCodePath;
    }
    
    
    

}
