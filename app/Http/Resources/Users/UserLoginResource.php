<?php

// app/Http/Resources/LoginResource.php
namespace App\Http\Resources\Users;

use Illuminate\Http\Resources\Json\JsonResource;

class UserLoginResource extends JsonResource
{    private $token;
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
            'success' => true,
            'message' => 'Login successfully',
            'token_type' => 'Bearer',
            'access_token' => $this->token,
            'data' => new UserResource($this->resource), // Include user details if needed
        ];
    }
}
