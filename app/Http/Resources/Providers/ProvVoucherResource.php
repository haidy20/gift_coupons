<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use PhpParser\Node\Expr\Cast\Double;

class ProvVoucherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'provider_id' => $this->provider_id,
            'random_num' => $this->random_num,
            'name' => $this->name,
            'amount' =>(double) $this->amount,
            'description' => $this->description,
            'is_active' => (bool)$this->is_active,
            'start_date' => $this->start_date,
            'duration_days' => $this->duration_days,
        ];
    }
}
