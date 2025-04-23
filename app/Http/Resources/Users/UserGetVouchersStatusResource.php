<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserGetVouchersStatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $voucher = $this->voucher;
        $pivot = $this->pivot;

        // تعيين اسم الحقل بناءً على الحالة
        $dateField = match ($pivot->status) {
            'active' => ['message' => 'Activation Date', 'date' => $pivot->purchase_date],
            'used' => ['message' => 'Usage Date', 'date' => $pivot->used_date],
            'expired' => ['message' => 'Expiration Date', 'date' => $pivot->expiry_date],
            default => ['message' => 'Date', 'date' => null],
        };

        return [
            'provider_name' => $voucher->provider->username ?? 'Unknown',
            'image' => $voucher->provider->media->file_path ?? 'Unknown',
            'latitude' => $voucher->provider->latitude ?? null,
            'longitude' => $voucher->provider->longitude ?? null,
            'location' => $voucher->provider->location ?? null,
            'id' => $voucher->id,
            'amount' => $voucher->amount,
            'random_num' => $voucher->random_num,
            'description' => $voucher->description,
            // 'date' => match ($pivot->status) {
            //     'active' => $pivot->purchase_date,
            //     'used' => $pivot->used_date,
            //     'expired' => 'Voucher expired',
            // },
            'status' => $pivot->status,
            'date_message' => $dateField['message'],
            'date' => $dateField['date'],
        ];
    }
}
