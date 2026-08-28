<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'code'           => $this->code,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'format'         => $this->format,
            'start_date'     => $this->start_date?->toDateString(),
            'end_date'       => $this->end_date?->toDateString(),
            'sessions'       => $this->sessions,
            'sessions_past'  => $this->sessions_past ?? $this->sessionsPast(),
            'hours'          => $this->hours,
            'price'          => $this->price,
            'category'       => $this->whenLoaded('category', fn () => $this->category?->name),
            'season'         => $this->whenLoaded('season', fn () => $this->season?->name),
            'students_count' => $this->when($this->students_count !== null, fn () => (int) $this->students_count),
        ];
    }
}
