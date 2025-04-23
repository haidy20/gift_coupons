<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserHomeCollection extends JsonResource
{
    public function toArray($request)
    {
        return [
            'category_name' => $this->category_name,
            'category_id'   => $this->id,
            'providers'     => $this->providers->map(function ($provider) {
                return [
                    'provider_id'    => $provider->id,
                    'provider_name'  => $provider->username,
                    'image'          => $provider->media->file_path ?? null,
                    'latitude'       => $provider->latitude,
                    'longitude'      => $provider->longitude,
                    'location'       => $provider->location,
                    'vouchers_count' => $provider->vouchers->where('is_active', 1)->count(),
                ];
            }),
        ];
    }
}