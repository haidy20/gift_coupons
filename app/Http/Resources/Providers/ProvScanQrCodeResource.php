<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvScanQrCodeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'random_num' => $this->voucher->random_num,
            'name' => $this->user->username,
            'phone' => $this->user->phone,
            'country' => $this->user->country->country_name ?? null,
            'price' => $this->voucher->amount,
            'used_date' => now()->toDateTimeString(),
        ];
    }
}
