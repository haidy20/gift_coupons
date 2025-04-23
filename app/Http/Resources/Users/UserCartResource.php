<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UserCartResource extends JsonResource
{
    public function toArray($request)
    {
        $voucherDetails = $this->vouchers->map(function ($voucher) {
            return [
                'provider_id' => $voucher->provider->id,
                'provider_name' => $voucher->provider->username,
                // 'provider_image' => optional($voucher->provider->media->first())->file_path,

                'latitude' => $voucher->provider->latitude,
                'longitude' => $voucher->provider->longitude,
                'location' => $voucher->provider->location,

                'voucher_id' => $voucher->id,
                'voucher_name' => $voucher->name,
                'amount' => $voucher->amount,
                'quantity' => $voucher->pivot->quantity,
                'total' => $voucher->amount * $voucher->pivot->quantity,
            ];
        });

        return [
            'vouchers' => $voucherDetails,
            'total_price' => $voucherDetails->sum('total'),
            'total_quantity' => $voucherDetails->sum('quantity'),
        ];
    }
}
