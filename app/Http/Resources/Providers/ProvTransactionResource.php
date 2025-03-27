<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvTransactionResource extends JsonResource
{
    private $randomNum; // تعريف متغير لحفظ `random_num`

    // ✅ تعديل الكونستركتور لاستقبال `random_num`
    public function __construct($resource, $randomNum = null)
    {
        parent::__construct($resource);
        $this->randomNum = $randomNum;
    }

    public function toArray(Request $request): array
    {
        return array_filter([
            'provider' => [
                'id' => $this->provider_id,
                'name' => $this->provider->username ?? null,
            ],
            'id' => $this->id,
            'transaction_type' => $this->transaction_type,
            'amount' => $this->amount,
            'checkout_id' => $this->transaction_type === 'withdrawal' ? null : $this->checkout_id,
            // ✅ إرجاع قيمة `random_num` الحقيقية فقط إذا كانت المعاملة "deposit"
            'random_num' => $this->transaction_type === 'deposit' ? $this->randomNum : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at, 
      
        ], function ($value) {
            return !is_null($value); // إزالة القيم null تلقائيًا
        });
    }
}
