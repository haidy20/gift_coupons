<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserRegisterResource extends JsonResource
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
            'user' => new UserResource($this),
            'access_token' => $this->token,
        ];
    }
}
