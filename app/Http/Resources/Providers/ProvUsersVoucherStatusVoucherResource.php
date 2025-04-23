<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvUsersVoucherStatusVoucherResource extends JsonResource
{
    
    public function toArray(Request $request)
    {
        
        return [
            'user_id' => $this->id,
            'user_name' => $this->username,
            'image' => $this->media->file_path ?? asset('images/default.png'),
            'phone' => $this->phone,
            'country' => $this->country->country_name,

            'voucher_id' => $this->pivot->voucher_id,

            'status' => $this->pivot->status,
            'date' => $this->pivot->status === 'active' ? $this->pivot->purchase_date :
                     ($this->pivot->status === 'used' ? $this->pivot->used_date :
                     'Voucher has expired'),
        ];
    }
}
