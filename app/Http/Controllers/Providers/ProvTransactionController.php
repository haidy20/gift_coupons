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
                'status' => 'error',
                'message' => 'Only providers can get transactions.',
                'data' => null
            ], 422);
        }

        $transactions = Transaction::where('provider_id', $provider->id)->get();

        return response()->json([
            'status' => 'success',
            'message' => 'All transactions retrived successfully',
            'transactions' => ProvTransactionResource::collection($transactions)
        ]);
    }

}
