<?php

use App\Models\Post;
use App\Models\User;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\getJson;

beforeEach(function () {
    actingAs(User::factory()->create());
});

test('returns available true when slug does not exist', function () {
    getJson('/admin/posts/slug/check?slug=my-unique-slug')
        ->assertSuccessful()
        ->assertJson(['available' => true]);
});

test('returns available false with suggested slug when slug is taken', function () {
    Post::factory()->create(['slug' => 'my-post']);

    getJson('/admin/posts/slug/check?slug=my-post')
        ->assertSuccessful()
        ->assertJson(['available' => false, 'suggested' => 'my-post-1']);
});

test('suggests next available suffix when multiple suffixes are taken', function () {
    Post::factory()->create(['slug' => 'my-post']);
    Post::factory()->create(['slug' => 'my-post-1']);

    getJson('/admin/posts/slug/check?slug=my-post')
        ->assertSuccessful()
        ->assertJson(['available' => false, 'suggested' => 'my-post-2']);
});

test('excludes current post when checking slug with exclude_id', function () {
    $post = Post::factory()->create(['slug' => 'my-post']);

    getJson("/admin/posts/slug/check?slug=my-post&exclude_id={$post->id}")
        ->assertSuccessful()
        ->assertJson(['available' => true]);
});

test('requires authentication to check slug', function () {
    auth()->logout();

    getJson('/admin/posts/slug/check?slug=any-slug')
        ->assertUnauthorized();
});
