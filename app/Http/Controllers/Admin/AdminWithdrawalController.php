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
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        // البحث عن طلب السحب
        $withdrawal = WithdrawalRequest::find($withdrawal_id);

        if (!$withdrawal) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.withdrawal_not_found'),
                'data' => null
            ], 404);
        }

        // التأكد من أن الحالة ما زالت "pending"
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.withdrawal_already_processed'),
                'data' => null
            ], 422);
        }

        // العثور على المحفظة والتأكد من وجود رصيد كافٍ
        $wallet = Wallet::where('provider_id', $withdrawal->provider_id)->first();

        if (!$wallet || $wallet->balance < $withdrawal->amount) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.insufficient_balance'),
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
        // $provider->notify(new GeneralNotification(
        //     'Withdrawal Approved',
        //     "Your withdrawal request of {$withdrawal->amount} has been approved."
        // ));

        $provider->notify(new GeneralNotification(
            trans('messages.withdrawal_approved_title'),
            trans('messages.withdrawal_approved_body', ['amount' => $withdrawal->amount])
        ));


        return response()->json([
            'status' => 'success',
            'message' => trans('messages.Withdrawal_approved_successfully'),
            'data' => new AdminWithdrawalRequestResource($withdrawal)
        ], 200);
    }

    public function rejectWithdrawal($withdrawal_id)
    {
        if (auth('api')->user()->role !== 'superAdmin') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.unauthorized_user'),
                'data' => null
            ], 403);
        }

        // البحث عن طلب السحب
        $withdrawal = WithdrawalRequest::find($withdrawal_id);

        if (!$withdrawal) {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.withdrawal_not_found'),
                'data' => null
            ], 404);
        }

        // التأكد من أن الحالة ما زالت "pending"
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'status' => 'fail',
                'message' => trans('messages.withdrawal_already_processed'),
                'data' => null

            ], 422);
        }

        // تحديث حالة السحب إلى rejected
        $withdrawal->update(['status' => 'rejected']);

        $provider = $withdrawal->provider;
        // إرسال إشعار للمزود باستخدام GeneralNotification
        // $provider->notify(new GeneralNotification(
        //     'Withdrawal Rejected',
        //     "Your withdrawal request of {$withdrawal->amount} has been rejected."
        // ));

        $provider->notify(new GeneralNotification(
            trans('messages.withdrawal_rejected_title'),
            trans('messages.withdrawal_rejected_body', ['amount' => $withdrawal->amount])
        ));


        return response()->json([
            'status' => 'success',
            'message' => trans('messages.Withdrawal_rejected'),
            'data' => new AdminWithdrawalRequestResource($withdrawal)
        ], 200);
    }
}
