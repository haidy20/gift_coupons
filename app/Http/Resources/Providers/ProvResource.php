<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // 'id' => $this->when($this->role == 'provider', $this->id), // تضمين id فقط إذا كان provider
            'id' => $this->id,
            'username' => $this->username,
            // 'email' => $this->when($this->role == 'provider', $this->email), // تضمين email فقط إذا كان provider
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => (bool)$this->is_active,
            'country' => $this->country->country_code,
        ];
    }
}
