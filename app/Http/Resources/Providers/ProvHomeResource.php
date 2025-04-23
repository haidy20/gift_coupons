<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvHomeResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'provider_name' => $this->provider->username,
            'image' => $this->provider->media->file_path ?? asset('images/default.png'),
            'Myvouchers' =>
            [
                'voucher_id' => $this->id,
                // 'voucher_name' => $this->name,
                'amount' => $this->amount,
                'purchased_vouchers' => $this->purchased_vouchers,
            ]
        ];
    }
}
