<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Voucher;

// Requests
use App\Http\Requests\Users\ToggleFavoriteVoucherRequest;
// Resources
use App\Http\Resources\Users\VoucherDetailsResourceResource;

class UserVoucherFavouriteController extends Controller
{
    // Toggle favorite voucher
    public function toggleFavoriteVoucher(ToggleFavoriteVoucherRequest $request)
    {
        $user = auth()->user();
        $voucherId = $request->validated()['voucher_id'];

        $isFavorite = $user->favoriteVouchers()->where('voucher_id', $voucherId)->exists();

        if ($isFavorite) {
            $user->favoriteVouchers()->detach($voucherId);
            return response()->json([
                'status' => 'success',
                'message' => 'Voucher removed from favorites',
                'data' => null
            ]);
        }

        $user->favoriteVouchers()->attach($voucherId);
        return response()->json([
            'status' => 'success',
            'message' => 'Voucher added to favorites',
            'data' => null
        ]);
    }

    // Get all fav vouchers
    public function getFavoriteVouchers()
    {
        $user = auth()->user(); // جلب المستخدم المصادق عليه

        $favoriteVouchers = Voucher::whereHas('favoritedByUsers', function ($query) {
            $query->where('user_id', auth()->id());
        })->with('provider')->get();

        if ($favoriteVouchers->isEmpty()) {
            return response()->json([
                'status' => 'fail',
                'message' => 'No favorite Vouchers found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Favorite vouchers retrieved successfully',
            'data' => VoucherDetailsResourceResource::collection($favoriteVouchers),

        ]);
    }
}
