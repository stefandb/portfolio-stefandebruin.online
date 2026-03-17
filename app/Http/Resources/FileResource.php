<?php

namespace App\Http\Resources;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin File */
class FileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'original_name' => $this->original_name,
            'url' => Storage::disk($this->disk)->url($this->path),
            'webp_url' => $this->webp_url,
            'thumbnail_url' => $this->thumbnail_url,
            'og_url' => $this->og_url,
            'responsive_urls' => $this->responsive_urls,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'is_image' => $this->isImage(),
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
