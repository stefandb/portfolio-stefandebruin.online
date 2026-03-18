<?php

namespace App\Models;

use Database\Factories\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read bool $is_image
 */
class File extends Model
{
    /** @use HasFactory<FileFactory> */
    use HasFactory;

    protected $appends = ['url', 'webp_url', 'thumbnail_url', 'og_url', 'responsive_urls', 'is_image'];

    protected $fillable = [
        'uuid',
        'name',
        'original_name',
        'path',
        'disk',
        'mime_type',
        'size',
        'webp_path',
        'thumbnail_path',
        'og_path',
        'responsive_paths',
    ];

    protected function casts(): array
    {
        return [
            'responsive_paths' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getIsImageAttribute(): bool
    {
        return $this->isImage();
    }

    private function variantUrl(?string $variantPath): ?string
    {
        if (! $variantPath) {
            return null;
        }

        return Storage::disk($this->disk)->url($variantPath);
    }

    public function getWebpUrlAttribute(): ?string
    {
        return $this->variantUrl($this->webp_path);
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->variantUrl($this->thumbnail_path);
    }

    public function getOgUrlAttribute(): ?string
    {
        return $this->variantUrl($this->og_path);
    }

    /** @return array<int, string>|null */
    public function getResponsiveUrlsAttribute(): ?array
    {
        if (empty($this->responsive_paths)) {
            return null;
        }

        /** @var array<int, string> $paths */
        $paths = $this->responsive_paths;

        return collect($paths)
            ->map(fn (string $path) => Storage::disk((string) $this->disk)->url($path))
            ->all();
    }
}
