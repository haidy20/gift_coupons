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
        $user = auth('api')->user();
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $voucher = Voucher::with(['provider.media']) // جلب البروفايدر مع الصور وجلب بيانات user_vouchers
            ->where('id', $voucherId)
            ->where('is_active', 1)
            ->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.voucher_expired'),
                'data' => null,
            ], 404);
        }

        // ✅ التحقق مما إذا كان الفاوتشر لم يبدأ بعد
        if (strtotime($voucher->start_date) > time()) {
            return response()->json([
                'status' => 'fail',
                // 'message' => 'This voucher cannot be purchased before ' . $voucher->start_date . '.',
                'message' => trans('messages.voucher_not_available_yet', ['date' => $voucher->start_date]),

                'data' => null,
            ], 400);
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
            'message' => trans('messages.voucher_added_to_cart'),
            'data' => new UserAddToCartResource($voucher),
        ]);
    }


    //  Delete from cart
    public function removeVoucherFromCart($voucherId)
    {
        $user = auth('api')->user();
        $cart = Cart::where('user_id', $user->id)->first();

        if (!$cart) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.cart_not_found'),
                'data' => null,
            ], 404);
        }

        // ✅ احضار كمية الفاوتشرات من نفس النوع
        $voucherQuantity = $cart->getVoucherQuantity($voucherId);

        if ($voucherQuantity == 0) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.voucher_not_found_in_cart'),
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
                'message' => trans('messages.voucher_removed_cart_empty'),
                'data' => null,
            ]);
        }

        $cart->update(['total_quantity' => $totalQuantity]);

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.voucher_removed_from_cart'),
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
        $cart = auth('api')->user()->cart()->with(['vouchers.provider.media'])->get();

        // if (!$cart) return null;
        if (!$cart) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.cart_is_empty'),
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.cart_details_retrieved'),
            'data' => UserCartResource::collection($cart),
        ]);
    }
}
