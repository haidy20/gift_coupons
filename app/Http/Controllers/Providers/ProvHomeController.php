<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Voucher;

// Requests
use App\Http\Requests\Providers\ProvGetUsersVoucherStatusRequest;

// Responses
use App\Http\Resources\Providers\ProvHomeResource;
use App\Http\Resources\Providers\ProvUsersVoucherStatusVoucherResource;


class ProvHomeController extends Controller
{
    // Home of provier
    public function home()
    {
        $provider = auth('api')->user();

        // جلب الفاوتشرات الخاصة بالمزود
        $vouchers = Voucher::where('provider_id', $provider->id)
            ->withCount([
                'users as times_purchased' => function ($query) use ($provider) {
                    $query->where('provider_id', $provider->id);
                }
            ])
            ->get(['id', 'name', 'amount']);

        return response()->json([
            'status' => 'success',
            'message' => 'Home retrived successfully',
            'provider_name' => $provider->username,
            'data' => ProvHomeResource::collection($vouchers),
        ]);
    }

    // Get Users who puchase vouchers(active,used,expired)status 
    public function getUsersByVoucherStatus(ProvGetUsersVoucherStatusRequest $request, $voucherId)
    {
        $provider = auth('api')->user();
        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access',
            ], 401);
        }
    
        $status = $request->query('status');
    
        $voucher = $provider->vouchers()->where('id', $voucherId)->first();
    
        if (!$voucher) {
            return response()->json([
                'status' => 'error',
                'message' => 'Voucher not found or does not belong to this provider',
                'data'=> null
            ], 404);
        }
    
        $users = $voucher->users()->wherePivot('status', $status)->get();
    
        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully by status',
            'voucher_amount' => $voucher->amount,
            'client_count' => $users->count(),
            'data' => ProvUsersVoucherStatusVoucherResource::collection($users),
        ]);
    }
    
}
