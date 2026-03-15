<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $uuid = (string) Str::uuid();
        $name = fake()->word();
        $extension = fake()->randomElement(['jpg', 'png', 'pdf', 'mp4']);
        $mimeMap = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'mp4' => 'video/mp4',
        ];

        return [
            'uuid' => $uuid,
            'name' => $name,
            'original_name' => "{$name}.{$extension}",
            'path' => "files/{$uuid}/{$name}.{$extension}",
            'disk' => 'public',
            'mime_type' => $mimeMap[$extension],
            'size' => fake()->numberBetween(1024, 5 * 1024 * 1024),
        ];
    }

    public function image(): static
    {
        return $this->state(fn () => [
            'mime_type' => fake()->randomElement(['image/jpeg', 'image/png', 'image/webp', 'image/gif']),
            'original_name' => fake()->word().'.jpg',
        ]);
    }
}
