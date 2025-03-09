<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MakeRegisterResource extends JsonResource
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
            'message' => 'Registration successfully',
            'status' => 200,
            'data' => new AdminMakeUsersResource($this),
            'access_token' => $this->token,
        ];
    }
}
