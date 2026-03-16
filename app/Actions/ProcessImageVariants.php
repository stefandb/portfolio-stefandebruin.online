<?php

namespace App\Actions;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;

class ProcessImageVariants
{
    /** Widths to generate for responsive srcset (px). */
    private const RESPONSIVE_WIDTHS = [480, 768, 1200];

    /** OG image dimensions (px). */
    private const OG_WIDTH = 1200;

    private const OG_HEIGHT = 630;

    /** Thumbnail dimensions (px). */
    private const THUMB_SIZE = 300;

    public function handle(File $file): void
    {
        if (! $file->isImage()) {
            return;
        }

        $sourcePath = Storage::disk($file->disk)->path($file->path);
        $dir = dirname($file->path);
        $stem = pathinfo($file->path, PATHINFO_FILENAME);

        $originalWidth = Image::load($sourcePath)->getWidth();

        $webpPath = $this->convertToWebp($sourcePath, $dir, $stem, $file->disk);
        $thumbnailPath = $this->createThumbnail($sourcePath, $dir, $stem, $file->disk);
        $ogPath = $this->createOgImage($sourcePath, $dir, $stem, $file->disk, $originalWidth);
        $responsivePaths = $this->createResponsiveVariants($sourcePath, $dir, $stem, $file->disk, $originalWidth);

        $file->update([
            'webp_path' => $webpPath,
            'thumbnail_path' => $thumbnailPath,
            'og_path' => $ogPath,
            'responsive_paths' => $responsivePaths,
        ]);
    }

    /**
     * Convert the original image to WebP at full resolution.
     */
    private function convertToWebp(string $sourcePath, string $dir, string $stem, string $disk): string
    {
        $path = "{$dir}/{$stem}.webp";

        Image::load($sourcePath)
            ->format('webp')
            ->save(Storage::disk($disk)->path($path));

        return $path;
    }

    /**
     * Create a 300×300 cropped WebP thumbnail.
     */
    private function createThumbnail(string $sourcePath, string $dir, string $stem, string $disk): string
    {
        $path = "{$dir}/{$stem}-thumb.webp";

        Image::load($sourcePath)
            ->fit(Fit::Crop, self::THUMB_SIZE, self::THUMB_SIZE)
            ->format('webp')
            ->save(Storage::disk($disk)->path($path));

        return $path;
    }

    /**
     * Create a 1200×630 cropped WebP OG image.
     */
    private function createOgImage(string $sourcePath, string $dir, string $stem, string $disk, int $originalWidth): string
    {
        $path = "{$dir}/{$stem}-og.webp";

        Image::load($sourcePath)
            ->fit(Fit::Crop, self::OG_WIDTH, self::OG_HEIGHT)
            ->format('webp')
            ->save(Storage::disk($disk)->path($path));

        return $path;
    }

    /**
     * Create WebP variants at each responsive breakpoint width.
     * Skips widths larger than the original to avoid upscaling.
     *
     * @return array<int, string>
     */
    private function createResponsiveVariants(string $sourcePath, string $dir, string $stem, string $disk, int $originalWidth): array
    {
        $paths = [];

        foreach (self::RESPONSIVE_WIDTHS as $width) {
            if ($width > $originalWidth) {
                continue;
            }

            $path = "{$dir}/{$stem}-{$width}w.webp";

            Image::load($sourcePath)
                ->width($width)
                ->format('webp')
                ->save(Storage::disk($disk)->path($path));

            $paths[$width] = $path;
        }

        return $paths;
    }
}
