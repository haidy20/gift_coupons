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
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access',
                'data' => null
            ], 403);
        }
    
        $vouchers = Voucher::all(); // ✅ جلب جميع القسائم لأن الأدمن يستطيع رؤية الكل
    
        return response()->json([
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => AdminVoucherResource::collection($vouchers)
        ]);
    }
    
}
