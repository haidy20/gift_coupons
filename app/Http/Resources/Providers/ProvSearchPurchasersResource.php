<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvSearchPurchasersResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'voucher_id' => $this->pivot->voucher_id,
            'user_name' => $this->username,
            'phone' => $this->phone,
            'purchase_date' => $this->pivot->purchase_date,
            'expiry_date' => $this->pivot->expiry_date,
            'used_date' => $this->pivot->used_date,
            'status' => $this->pivot->status,
        ];
    }
}
