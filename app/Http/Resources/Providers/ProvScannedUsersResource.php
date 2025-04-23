<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvScannedUsersResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'random_num' => $this->voucher->random_num,
            'image' => $this->user->media->file_path ?? asset('images/default.png'),
            'name' => $this->user->username,
            'phone' => $this->user->phone,
            'country' => $this->user->country->country_name ?? null,
            'price' => $this->voucher->amount,
            'used_date' => $this->created_at->toDateTimeString(),
        ];
    }
}
