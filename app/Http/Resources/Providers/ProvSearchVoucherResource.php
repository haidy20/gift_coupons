<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvSearchVoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'random_num' => $this->random_num,
            // 'provider_name' => $this->provider->username ?? 'N/A',
            // 'phone' => $this->provider->phone ?? 'N/A',
            // 'country' => $this->provider->country->country_name ?? 'N/A',

            'users' => ProvUserVoucherResource::collection($this->users), // ✅ استخدم Resource Collection للمستخدمين
        ];
    }
}
