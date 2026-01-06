<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            'isbn' => $this->isbn,
            'published_date' => $this->published_date->format('Y-m-d'),
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
            'average_rating' => round($this->reviews_avg_rating ?? 0, 2),
            'review_count' => $this->reviews_count ?? 0,
        ];
    }
}
