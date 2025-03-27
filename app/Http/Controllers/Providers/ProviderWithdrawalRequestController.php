<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;

use App\Models\Wallet;
use App\Models\UsersAccount;
use App\Models\WithdrawalRequest;
use App\Notifications\GeneralNotification;

// Requests
use App\Http\Requests\Providers\ProviderWithdrawalRequest;
// Resources
use App\Http\Resources\Providers\ProvWithdrawalResource;

class ProviderWithdrawalRequestController extends Controller
{
    public function withdraw(ProviderWithdrawalRequest $request)
    {
        $provider = auth('api')->user();

        $wallet = Wallet::where('provider_id', $provider->id)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Wallet not found or Unauthnticated'], 404);
        }

        $withdrawal = WithdrawalRequest::create(array_merge(
            ['provider_id' => $provider->id,
            'status' => "pending"],
            $request->validated()
        ));
        
        // ✅ إرسال إشعار لكل الأدمنز
        $superAdmin = UsersAccount::where('role', 'superAdmin')->first();
        $superAdmin->notify(new GeneralNotification('Withdrawal Request', "Provider {$provider->username} submitted a withdrawal request and is waiting for approval."));


        return response()->json([
            'message'    => 'Successful withdrawal',
            'withdrawal' => new ProvWithdrawalResource($withdrawal)
        ]);
    }
}
