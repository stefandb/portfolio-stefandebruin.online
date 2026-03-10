<?php

use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('projects index page is displayed', function () {
    $user = User::factory()->create();
    Project::factory()->count(5)->create();

    actingAs($user)
        ->get(route('admin.projects.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 5)
        );
});

test('projects can be filtered by search', function () {
    $user = User::factory()->create();
    Project::factory()->create(['title' => 'Laravel Project']);
    Project::factory()->create(['title' => 'Vue Project']);

    actingAs($user)
        ->get(route('admin.projects.index', ['search' => 'Laravel']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 1)
            ->where('projects.data.0.title', 'Laravel Project')
        );
});

test('projects can be filtered by status', function () {
    $user = User::factory()->create();
    Project::factory()->create(['status' => 'published']);
    Project::factory()->create(['status' => 'draft']);

    actingAs($user)
        ->get(route('admin.projects.index', ['status' => 'published']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Projects/Index')
            ->has('projects.data', 1)
            ->where('projects.data.0.status', 'published')
        );
});
