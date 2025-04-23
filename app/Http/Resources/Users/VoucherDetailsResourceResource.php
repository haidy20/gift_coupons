<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoucherDetailsResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'provider' => [
                'id'        => $this->provider->id,
                'username'  => $this->provider->username,
                'image'     => $this->provider->media->first()->file_path ?? null,
                'latitude'  => $this->provider->latitude,
                'longitude' => $this->provider->longitude,
                'location'  => $this->provider->location,
                // 'category'  => $this->provider->category ? $this->provider->category->category_name : null,
            ],
            'favoriteVouchers' => [
                'id'          => $this->id,
                'name'        => $this->name,
                'amount'      => $this->amount,
                'description' => $this->description,
            ],
        ];
    }
}
