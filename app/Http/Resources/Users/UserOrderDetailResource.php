<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderDetailResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'voucher_id' => $this->voucher_id,
            'voucher_name' => $this->voucher->name,
            'quantity' => $this->quantity,
            'amount' => $this->amount,
            'total_price' => $this->amount * $this->quantity,
        ];
    }
}
