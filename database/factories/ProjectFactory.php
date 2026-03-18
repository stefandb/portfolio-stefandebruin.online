<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence(3);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => $this->faker->paragraph(),
            'content' => $this->faker->paragraphs(3, true),
            'github_url' => $this->faker->optional()->url(),
            'demo_url' => $this->faker->optional()->url(),
            'company' => $this->faker->optional()->company(),
            'role' => $this->faker->optional()->jobTitle(),
            'year' => $this->faker->year(),
            'status' => $this->faker->randomElement(['draft', 'published']),
            'published_at' => $this->faker->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
