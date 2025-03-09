<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminAboutUsTransResource extends JsonResource
{
       public function toArray($request)
    {
        return [
            'id' => $this->id,
            'translations' => $this->translations->map(function ($translation) {
                return [
                    'locale' => $translation->locale,
                    'title' => $translation->title,
                    'description' => $translation->description,
                ];
            }),
        ];
    }
}
