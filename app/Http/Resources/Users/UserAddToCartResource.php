<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UserAddToCartResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'provider_id' => $this->provider->id,
            'provider_name' => $this->provider->username,
            // 'provider_image' => optional($this->provider->media->first())->file_path,

            'latitude' => $this->provider->latitude,
            'longitude' => $this->provider->longitude,
            'location' => $this->provider->location,

            'voucher_id' => $this->id,
            'voucher_name' => $this->name,
            'amount' => $this->amount,
            'quantity' => $this->pivot->quantity,
            'total' => $this->amount * $this->pivot->quantity,
        ];
    }
}
