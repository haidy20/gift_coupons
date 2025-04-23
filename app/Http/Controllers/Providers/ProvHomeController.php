<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;


// Requests
use App\Http\Requests\Providers\ProvGetUsersVoucherStatusRequest;

// Responses
use App\Http\Resources\Providers\ProvHomeResource;
use App\Http\Resources\Providers\ProvUsersVoucherStatusVoucherResource;


class ProvHomeController extends Controller
{
    // public function home()
    // {
    //     $provider = auth('api')->user();

    //     // جلب الفاوتشرات الخاصة بالمزود
    //     $vouchers = Voucher::where('provider_id', $provider->id)
    //         ->withCount([
    //             'users as times_purchased' => function ($query) use ($provider) {
    //                 $query->where('provider_id', $provider->id);
    //             }
    //         ])
    //         ->get(['id', 'name', 'amount']);

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Home retrived successfully',
    //         // 'provider_name' => $provider->username,
    //         'data' => ProvHomeResource::collection($vouchers),
    //     ]);
    // }

    // Home of provider
    public function home()
    {
        $provider = auth('api')->user();
    
        $vouchers = Voucher::where('provider_id', $provider->id)
            ->whereHas('users', function (Builder $query) {
                $query->whereIn('user_vouchers.status', ['active', 'used', 'expired']);
            })
            ->withCount([
                'users as purchased_vouchers' => function ($query) {
                    $query->whereIn('user_vouchers.status', ['active', 'used', 'expired']);
                }
            ])
            ->get(['id', 'provider_id', 'amount']);
    
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.home_retrieved_successfully'),
            'data' => ProvHomeResource::collection($vouchers),
        ]);
    }
    

    // Get Users who puchase vouchers(active,used,expired)status 
    public function getUsersByVoucherStatus(ProvGetUsersVoucherStatusRequest $request, $voucherId)
    {
        $provider = auth('api')->user();
        if (!$provider) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Unauthorized access',
            ], 401);
        }

        $status = $request->query('status');

        $voucher = $provider->vouchers()->where('id', $voucherId)->first();

        if (!$voucher) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Voucher not found or does not belong to this provider',
                'data' => null
            ], 404);
        }

        $users = $voucher->users()->wherePivot('status', $status)->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.user_retrieved_by_status'),
            'data' => ProvUsersVoucherStatusVoucherResource::collection($users),
        ]);
    }
}
