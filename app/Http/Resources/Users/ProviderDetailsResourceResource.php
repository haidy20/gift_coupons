<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProviderDetailsResourceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'image'         => $this->media->first()->file_path ?? null,
            'latitude'      => $this->latitude,
            'longitude'     => $this->longitude,
            'location'      => $this->location,
            // 'category'      => $this->category ? $this->category->category_name : null,
            'vouchers_count' => $this->vouchers->where('is_active', 1)->count(),
            'vouchers'      => VoucherResource::collection($this->vouchers->where('is_active', 1)),
        ];
    }
}
