<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;

// Resources
use App\Http\Resources\Providers\ProvWalletResource;

class ProvWalletController extends Controller
{
    //  Get the wallet details for the logged-in provider (Provider)
    public function getWallet()
    {
        $provider = auth('api')->user();

        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.only_providers_can_get_wallet_balance'),
                'data' => null
            ], 422);
        }
        
        $wallet = Wallet::where('provider_id', $provider->id)->first();

        if (!$wallet) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.wallet_not_found_or_unauthenticated'),
                'data' => null
            ], 404);
        }
    
        return response()->json([
            'status' => 'success',
            'message' => trans('messages.wallet_details_retrieved_successfully'),
            'data' => new ProvWalletResource($wallet)
        ], 200);
    }
}
