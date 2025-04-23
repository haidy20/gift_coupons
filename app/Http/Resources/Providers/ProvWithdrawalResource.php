<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvWithdrawalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'provider_id'    => $this->provider_id,
            'amount'         => $this->amount,
            'bank_name'      => $this->bank_name,
            'account_number' => $this->account_number,
            'iban'           => $this->iban,
        ];
    }
}
