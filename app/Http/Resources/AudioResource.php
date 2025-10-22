<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AudioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'description'  => $this->description,
            'category'     => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'mime_type'    => $this->mime_type,
            'size_bytes'   => $this->size_bytes,
            'duration_sec' => $this->duration_sec,
            'created_at'   => $this->created_at,
            'links'        => [
                'download_signed' => route('api.v1.audios.download', $this->id),
            ],
        ];
    }
}
