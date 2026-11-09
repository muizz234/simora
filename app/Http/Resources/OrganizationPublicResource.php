<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Representasi publik read-only organisasi untuk landing & konsumen API.
 * Hanya field yang memang untuk publik (tanpa data internal).
 */
class OrganizationPublicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'full_name' => $this->full_name,
            'focus' => $this->focus,
            'period' => $this->period,
            'description' => $this->description,
            'vision' => $this->vision,
            'mission' => $this->mission,
            'banner_color' => $this->banner_color,
            'accent_color' => $this->accent_color,
            'logo_url' => $this->logo_url,
            'registration_url' => $this->registration_url,
            'sort_order' => $this->sort_order,
        ];
    }
}
