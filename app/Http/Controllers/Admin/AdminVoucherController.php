<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;

// Resources
use App\Http\Resources\Admin\AdminVoucherResource;

class AdminVoucherController extends Controller
{
    public function getVouchers()
    {
        $user = auth('api')->user();

        // ✅ التأكد من أن المستخدم هو Admin فقط
        if ($user->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        $vouchers = Voucher::all(); // ✅ جلب جميع القسائم لأن الأدمن يستطيع رؤية الكل

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.vouchers_retrieved_successfully'),
            'data' => AdminVoucherResource::collection($vouchers)
        ]);
    }
}
