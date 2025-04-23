<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAllStatusVouchersResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'description' => $this->description,
            'status' => $this->whenPivotLoaded('user_vouchers', function () {
                return $this->pivot->status;
            }),
            'purchase_date' => $this->whenPivotLoaded('user_vouchers', function () {
                return $this->pivot->purchase_date;
            }),
            'expiry_date' => $this->whenPivotLoaded('user_vouchers', function () {
                return $this->pivot->expiry_date;
            }),
            'used_date' => $this->whenPivotLoaded('user_vouchers', function () {
                return $this->pivot->used_date;
            }),
            'provider' => [
                'id' => $this->provider->id,
                'username' => $this->provider->username,
            ],
        ];
    }
}
