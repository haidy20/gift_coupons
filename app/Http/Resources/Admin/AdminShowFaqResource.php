<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminShowFaqResource extends JsonResource
{
    protected $locale;

    public function __construct($resource, $locale)
    {
        parent::__construct($resource);
        $this->locale = $locale;
    }

    public function toArray(Request $request): array
    {
        $translation = $this->translations->where('locale', $this->locale)->first();

        if (!$translation) {
            return [
                'id' => $this->id,
                'message' => 'Translation not found for this locale'
            ];
        }

        return [
            'id' => $this->id,
            'locale' => $this->locale,
            'question' => $translation->question,
            'answer' => $translation->answer,
        ];
    }
}
