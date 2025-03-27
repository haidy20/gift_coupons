<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WithdrawalRequest;
use App\Models\Wallet;
use App\Models\Transaction;

use App\Notifications\GeneralNotification;
// Resources
use App\Http\Resources\Admin\AdminWithdrawalRequestResource;

class AdminWithdrawalController extends Controller
{
    public function approveWithdrawal($withdrawal_id)
    {
        if (auth('api')->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only superAdmin can approve withdrawal.',
                'data' => null
            ], 403);
        }

        // البحث عن طلب السحب
        $withdrawal = WithdrawalRequest::find($withdrawal_id);

        if (!$withdrawal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal request not found.',
                'data' => null
            ], 404);
        }

        // التأكد من أن الحالة ما زالت "pending"
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal request was already processed.',
                'data' => null
            ], 422);
        }

        // العثور على المحفظة والتأكد من وجود رصيد كافٍ
        $wallet = Wallet::where('provider_id', $withdrawal->provider_id)->first();

        if (!$wallet || $wallet->balance < $withdrawal->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance in wallet.',
                'data' => null
            ], 400);
        }

        // تحديث حالة السحب إلى approved وخصم المبلغ
        $withdrawal->update(['status' => 'approved']);
        $wallet->decrement('balance', $withdrawal->amount);

        // Create a transaction
        Transaction::create([
            'transaction_code' => 'TXN-' . strtoupper(uniqid()),
            'transaction_type' => 'withdrawal',
            'provider_id' => $withdrawal->provider_id,
            'amount' => $withdrawal->amount,
        ]);

        $provider = $withdrawal->provider;
        // إرسال إشعار للمزود باستخدام GeneralNotification
        $provider->notify(new GeneralNotification(
            'Withdrawal Approved',
            "Your withdrawal request of {$withdrawal->amount} has been approved."
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal approved successfully.',
            'data' => new AdminWithdrawalRequestResource($withdrawal)
        ], 200);
    }

    public function rejectWithdrawal($withdrawal_id)
    {
        if (auth('api')->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Only superAdmin can reject withdrawal.',
                'data' => null
            ], 403);
        }

        // البحث عن طلب السحب
        $withdrawal = WithdrawalRequest::find($withdrawal_id);

        if (!$withdrawal) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal request not found.',
                'data' => null
            ], 404);
        }

        // التأكد من أن الحالة ما زالت "pending"
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdrawal request was already processed.',
                'data' => null

            ], 422);
        }

        // تحديث حالة السحب إلى rejected
        $withdrawal->update(['status' => 'rejected']);

        $provider = $withdrawal->provider;
        // إرسال إشعار للمزود باستخدام GeneralNotification
        $provider->notify(new GeneralNotification(
            'Withdrawal Rejected',
            "Your withdrawal request of {$withdrawal->amount} has been rejected."
        ));

        return response()->json([
            'status' => 'success',
            'message' => 'Withdrawal request rejected.',
            'data' => new AdminWithdrawalRequestResource($withdrawal)
        ], 200);
    }
}
