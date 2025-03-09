<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use App\Models\Cart;
use App\Models\Voucher;
// Responses
use App\Http\Resources\Users\UserCartResource;
use App\Http\Resources\Users\UserAddToCartResource;


class UserCartController extends Controller
{
    public function addVoucherToCart($voucherId)
    {
        $user = auth()->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $voucher = Voucher::with(['provider.media']) // ✅ جلب البروفايدر مع الصور
            ->where('id', $voucherId)
            ->where('is_active', 1)
            ->first();

            if (!$voucher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or unavailable voucher.',
                    'data' => null,
                ], 404);
            }

        // ✅ احضار كمية الفاوتشرات من نفس النوع
        $voucherQuantity = $cart->getVoucherQuantity($voucherId);

        if ($voucherQuantity > 0) {
            // ✅ زيادة الكمية إذا كان الفاوتشر موجودًا بالفعل
            DB::table('cart_voucher')
                ->where('cart_id', $cart->id)
                ->where('voucher_id', $voucherId)
                ->increment('quantity');
        } else {
            // ✅ إضافة الفاوتشر إلى السلة لأول مرة
            DB::table('cart_voucher')->insert([
                'cart_id' => $cart->id,
                'voucher_id' => $voucherId,
                'quantity' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ✅ تحديث العدد الإجمالي لكل الفاوتشرات في السلة
        $totalQuantity = DB::table('cart_voucher')->where('cart_id', $cart->id)->sum('quantity');
        $cart->update(['total_quantity' => $totalQuantity]);

        // ✅ جلب بيانات السلة بعد التحديث بنفس التنسيق المطلوب
        $voucher = $cart->vouchers()->where('voucher_id', $voucherId)->with(['provider.media'])->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Voucher added to cart successfully.',
            'data' => new UserAddToCartResource($voucher),
        ]);
    }


    //  Delete from cart
    public function removeVoucherFromCart($voucherId)
    {
        $user = auth()->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart not found.',
                'data' => null,
            ], 404);
        }

        // ✅ احضار كمية الفاوتشرات من نفس النوع
        $voucherQuantity = $cart->getVoucherQuantity($voucherId);

        if ($voucherQuantity == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found in cart.',
                'data' => null,
            ], 400);
        }

        if ($voucherQuantity > 1) {
            // ✅ تقليل الكمية إذا كان الفاوتشر موجودًا بأكثر من نسخة
            DB::table('cart_voucher')
                ->where('cart_id', $cart->id)
                ->where('voucher_id', $voucherId)
                ->decrement('quantity');
        } else {
            // ✅ حذف الفاوتشر بالكامل إذا كانت الكمية = 1
            DB::table('cart_voucher')->where('cart_id', $cart->id)->where('voucher_id', $voucherId)->delete();
        }

        // ✅ تحديث العدد الإجمالي لكل الفاوتشرات في السلة
        $totalQuantity = DB::table('cart_voucher')->where('cart_id', $cart->id)->sum('quantity');

        if ($totalQuantity == 0) {
            // ✅ حذف السلة إذا لم يبقَ أي عنصر فيها
            $cart->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Voucher removed .Cart is empty now.',
                'data' => null,
            ]);
        }

        $cart->update(['total_quantity' => $totalQuantity]);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher removed from cart successfully.',
            'data' => [
                'cart_id' => $cart->id,
                'voucher_quantity' => max(0, $voucherQuantity - 1), // ✅ عدد الفاوتشرات من نفس النوع بعد الحذف
                'total_quantity' => $totalQuantity, // ✅ العدد الإجمالي لكل الفاوتشرات
            ],
        ]);
    }

    // Show cart 
    public function getCartDetails()
    {
        $cart = auth()->user()->cart()->with(['vouchers.provider.media'])->get();

        // if (!$cart) return null;
        if (!$cart) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cart is empty.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Cart details retrieved successfully.',
            'data' => UserCartResource::collection($cart),
        ]);
    }
}
