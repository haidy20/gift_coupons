<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminMediaResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'file_path' => $this->file_path,
        ];
    }
}
