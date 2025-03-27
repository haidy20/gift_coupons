<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvHomeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // 'provider_name' => auth('api')->user()->username, // إضافة اسم البروفايدر
            'voucher_id' => $this->id,
            // 'voucher_name' => $this->name,
            'amount' => $this->amount,
            'purchased_vouchers' => $this->times_purchased, // عدد مرات الشراء المحسوبة
        ];
    }
}
