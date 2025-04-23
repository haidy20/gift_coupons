<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Voucher;
use App\Models\OrderDetail;
use App\Models\UsersAccount;





// Resources
use App\Http\Resources\Providers\ProvTransactionResource;

class ProvTransactionController extends Controller
{
    /**
     * List all transactions for the logged-in provider.
     */
    public function getTransactions()
    {
        $provider = auth('api')->user();

        if ($provider->role !== 'provider') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.only_providers_can_get_transactions'),
                'data' => null
            ], 422);
        }

        $transactions = Transaction::where('provider_id', $provider->id)->get();

        return response()->json([
            'status' => 'success',
            'message' => trans('messages.all_transactions_retrieved_successfully'),
            'transactions' => ProvTransactionResource::collection($transactions)
        ]);
    }

}
