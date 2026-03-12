<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->word(),
            'slug' => $this->faker->word(),
            'excerpt' => $this->faker->word(),
            'content' => $this->faker->word(),
            'reading_time' => $this->faker->randomNumber(),
            'status' => $this->faker->word(),
            'published_at' => Carbon::now(),
            'series_id' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
