<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Support\Facades\Auth;

// Requests
use App\Http\Requests\Providers\ProvVoucherRequest;
use App\Http\Requests\Providers\ProvSearchPurchasersRequest;



// Resources
use App\Http\Resources\Providers\ProvVoucherResource;
use App\Http\Resources\Providers\ProvSearchPurchasersResource;



class ProvVoucherController extends Controller
{
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

    // Search of purchasers by id of voucher
    public function searchVouchers(ProvSearchPurchasersRequest $request)
    {
        $provider = auth('api')->user();
        $voucherId = $request->query('query');
    
        // Fetch vouchers belonging to the provider
        $purchasers = Voucher::where('provider_id', $provider->id)
            ->when($voucherId, fn($query) => $query->where('id', $voucherId))
            ->with('users:id,username,phone')
            ->get()
            ->pluck('users')
            ->flatten();
    
        return response()->json([
            'success' => true,
            'message' => 'The search results were retrieved successfully.',
            'data' => ProvSearchPurchasersResource::collection($purchasers),
        ]);
    }
    
    
    
    
    
}
