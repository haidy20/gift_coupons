<?php

namespace App\Http\Resources\Providers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProvLoginResource extends JsonResource
{
    private $token;
    private $expiresIn;

   public function __construct($resource, $token, $expiresIn)
   {
       parent::__construct($resource);
       $this->token = $token;
       $this->expiresIn = $expiresIn;
   }

   public function toArray($request)
   {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->token,
            'expires_in' => $this->expiresIn,
            'provider' => new ProvResource($this->resource),
        ];
   }
}
