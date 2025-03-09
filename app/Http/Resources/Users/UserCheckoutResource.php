<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'user_id' => $this->user_id,
            'total_quantity' => $this->total_quantity,
            'total_for_all' => $this->total_for_all,
            'order_details' => $this->orderDetails->map(function ($orderDetail) {
                return [
                    'voucher_id' => $orderDetail->voucher->id,
                    'voucher_name' => $orderDetail->voucher->name,
                    'quantity' => $orderDetail->voucher->pivot->quantity ?? 1, // افتراضي 1 لو لم يكن هناك كمية
                    'amount' => $orderDetail->voucher->amount,
                    'total_price' => $orderDetail->voucher->amount * ($orderDetail->voucher->pivot->quantity ?? 1),
                ];
            }),
            'status_message' => "Order placed successfully with {$this->total_quantity} items totaling \${$this->total_for_all}."
        ];
    }
}
