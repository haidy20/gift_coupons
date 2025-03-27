<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminLoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->token, // التوكن JWT
            'expires_in' => $this->expires_in, // مدة صلاحية التوكن
        ];
    }
}
