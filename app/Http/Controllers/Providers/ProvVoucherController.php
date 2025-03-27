<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\SearchHistory;



// Requests
use App\Http\Requests\Providers\ProvVoucherRequest;
use App\Http\Requests\Providers\ProvSearchPurchasersRequest;

// Resources
use App\Http\Resources\Providers\ProvVoucherResource;
use App\Http\Resources\Providers\ProvSearchVoucherResource;
// use App\Http\Resources\Providers\ProvUserVoucherResource;




class ProvVoucherController extends Controller
{
       /**
     * 📌 انشاء بيانات القسيمة
     */
    public function createVoucher(ProvVoucherRequest $request)
    {
        $user = auth('api')->user(); // 🔐 جلب المستخدم المصادق عليه

        if ($user->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null
            ], 403);
        }

        $voucher = Voucher::create([
            'provider_id' => $user->id,
            'random_num' => mt_rand(100000, 999999),
            'name' => $request->name,
            'amount' => $request->amount,
            'description' => $request->description,
            'is_active' => false,
            'start_date' => $request->start_date,
            'duration_days' => $request->duration_days,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher created successfully',
            'data' => new ProvVoucherResource($voucher)
        ], 200);
    }

    /**
     * 📌 تعديل بيانات القسيمة
     */
    public function updateVoucher(ProvVoucherRequest $request, $voucherId)
    {
        $user = auth('api')->user();

        $voucher = Voucher::where('id', $voucherId)->where('provider_id', $user->id)->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found or unauthorized',
                'data' => null
            ], 404);
        }

        $voucher->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher updated successfully',
            'data' => new ProvVoucherResource($voucher)
        ]);
    }

    /**
     * 📌 حذف القسيمة
     */
    public function deleteVoucher($voucherId)
    {
        $user = auth('api')->user();

        $voucher = Voucher::where('id', $voucherId)->where('provider_id', $user->id)->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found or unauthorized',
                'data' => null
            ], 404);
        }

        $voucher->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher deleted successfully',
            'data' => null
        ]);
    }

    /**
     * 📌 عرض قسيمة معينة بالتفاصيل
     */
    public function getVoucher($voucherId)
    {
        $user = auth('api')->user();

        $voucher = Voucher::where('id', $voucherId)->where('provider_id', $user->id)->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found or unauthorized',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Voucher retrieved successfully',
            'data' => new ProvVoucherResource($voucher)
        ]);
    }

    /**
     * 📌 عرض جميع القسائم الخاصة بالمزود
     */
    public function getVouchers()
    {
        $user = auth('api')->user();

        $vouchers = Voucher::where('provider_id', $user->id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => ProvVoucherResource::collection($vouchers)
        ]);
    }

    /**
     * 📌 تفعيل القسيمة
     */
    public function toggleVoucherStatus($voucherId)
    {
        $user = auth('api')->user();

        $voucher = Voucher::where('id', $voucherId)->where('provider_id', $user->id)->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found or unauthorized',
                'data' => null
            ], 404);
        }

        $voucher->is_active = !$voucher->is_active;
        $voucher->save();

        return response()->json([
            'status' => 'success',
            'message' => $voucher->is_active ? 'Voucher activated successfully' : 'Voucher deactivated successfully',
            'data' => new ProvVoucherResource($voucher)
        ]);
    }

    // Search voucher by name or rundom_num whatever its status to clients
    public function getVoucherByNameOrRandomNum(ProvSearchPurchasersRequest $request)
    {
        $user = auth('api')->user();

        if ($user->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null
            ], 403);
        }

        // التحقق من وجود القيمة في الـ query parameters
        $voucherParam = $request->query('voucherParam');

        if (!$voucherParam) {
            return response()->json([
                'status' => 'error',
                'message' => 'voucherParam is required',
                'data' => null
            ], 400);
        }

        // Save the search query in the history table
        SearchHistory::create([
            'provider_id' => $user->id,
            'search_query' => $voucherParam,
        ]);

        // البحث فقط عن الفاوتشرات الخاصة بالمزود الحالي
        $vouchers = Voucher::with(['users', 'provider'])
            ->where('provider_id', $user->id)
            ->where(function ($query) use ($voucherParam) {
                $query->where('name', 'LIKE', "%{$voucherParam}%")
                    ->orWhere('random_num', 'LIKE', "%{$voucherParam}%");
            })
            ->whereHas('users') // تصفية الفاوتشرات التي تحتوي على مستخدمين فقط
            ->get();


        if ($vouchers->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No vouchers found or you do not have access to search about it ',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Vouchers retrieved successfully',
            'data' => ProvSearchVoucherResource::collection($vouchers),
        ]);
    }
    // Delete search history
    public function deleteSearchHistory()
    {
        $user = auth('api')->user();

        if ($user->role !== 'provider') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null
            ], 403);
        }

        // Delete the provider's search history
        SearchHistory::where('provider_id', $user->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Search history deleted successfully',
            'data' => null
        ]);
    }
}
