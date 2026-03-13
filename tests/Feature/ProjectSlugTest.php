<?php

use App\Models\Project;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function () {
    actingAs(User::factory()->create());
});

test('returns available true when slug does not exist', function () {
    getJson('/admin/projects/slug/check?slug=my-unique-slug')
        ->assertSuccessful()
        ->assertJson(['available' => true]);
});

test('returns available false with suggested slug when slug is taken', function () {
    Project::factory()->create(['slug' => 'my-project']);

    getJson('/admin/projects/slug/check?slug=my-project')
        ->assertSuccessful()
        ->assertJson(['available' => false, 'suggested' => 'my-project-1']);
});

test('suggests next available suffix when multiple suffixes are taken', function () {
    Project::factory()->create(['slug' => 'my-project']);
    Project::factory()->create(['slug' => 'my-project-1']);

    getJson('/admin/projects/slug/check?slug=my-project')
        ->assertSuccessful()
        ->assertJson(['available' => false, 'suggested' => 'my-project-2']);
});

test('excludes current project when checking slug with exclude_id', function () {
    $project = Project::factory()->create(['slug' => 'my-project']);

    getJson("/admin/projects/slug/check?slug=my-project&exclude_id={$project->id}")
        ->assertSuccessful()
        ->assertJson(['available' => true]);
});

test('requires authentication to check slug', function () {
    auth()->logout();

    getJson('/admin/projects/slug/check?slug=any-slug')
        ->assertUnauthorized();
});
