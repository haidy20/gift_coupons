<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvUserVoucherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pivot = $this->pivot;

        $usedDate = $pivot->used_date ? date('Y-m-d H:i:s', strtotime($pivot->used_date)) : null;
        $expiryDate = $pivot->expiry_date ? date('Y-m-d H:i:s', strtotime($pivot->expiry_date)) : null;

        $isExpired = $pivot->expiry_date && strtotime($pivot->expiry_date) < time() && is_null($pivot->used_date);
        $isUsed = !is_null($pivot->used_date);
        // $isActive = !$isUsed && !$isExpired;

        return [
            'user_name' => $this->username,
            'image' => $this->media->file_path ?? asset('images/default.png'),
            'phone' => $this->phone ?? 'N/A',
            'country' => $this->country->country_name ?? 'N/A',

            'status' => $isUsed ? 'used' : ($isExpired ? 'expired' : 'active'),
            'voucher_status' => $isUsed 
                ? "Used on " . ($usedDate ?? "Unknown") 
                : ($isExpired 
                    ? "Expired" 
                    : "Valid until " . ($expiryDate ?? "Unknown")),
        ];
    }
}
