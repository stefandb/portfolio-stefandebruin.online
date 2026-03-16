<?php

use App\Models\File;
use App\Models\Post;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    actingAs(User::factory()->create());
});

test('posts index page is displayed', function () {
    Post::factory()->count(5)->create();

    $this->get(route('admin.posts.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Index')
            ->has('posts.data', 5)
        );
});

test('posts can be filtered by search', function () {
    Post::factory()->create(['title' => 'Laravel Tutorial']);
    Post::factory()->create(['title' => 'Vue Tips']);

    $this->get(route('admin.posts.index', ['search' => 'Laravel']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Laravel Tutorial')
        );
});

test('posts can be filtered by status', function () {
    Post::factory()->published()->create();
    Post::factory()->draft()->create();

    $this->get(route('admin.posts.index', ['status' => 'published']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.status', 'published')
        );
});

test('create page is displayed', function () {
    $this->get(route('admin.posts.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Create')
            ->has('availableSeries')
        );
});

test('post can be stored', function () {
    $this->post(route('admin.posts.store'), [
        'title' => 'My First Post',
        'slug' => 'my-first-post',
        'excerpt' => 'A short summary.',
        'content' => 'Full content here.',
        'status' => 'draft',
    ])->assertRedirect(route('admin.posts.index'));

    expect(Post::where('slug', 'my-first-post')->exists())->toBeTrue();
});

test('post can be stored with file references', function () {
    $files = File::factory()->count(2)->create();

    $this->post(route('admin.posts.store'), [
        'title' => 'Post with Images',
        'slug' => 'post-with-images',
        'content' => 'Content.',
        'status' => 'draft',
        'image_uuids' => $files->pluck('uuid')->all(),
    ])->assertRedirect(route('admin.posts.index'));

    $post = Post::where('slug', 'post-with-images')->first();

    expect($post->files)->toHaveCount(2);
});

test('edit page is displayed', function () {
    $post = Post::factory()->create();

    $this->get(route('admin.posts.edit', $post))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Admin/Posts/Edit')
            ->has('availableSeries')
            ->where('post.id', $post->id)
        );
});

test('post can be updated', function () {
    $post = Post::factory()->create(['title' => 'Old Title', 'slug' => 'old-title']);

    $this->patch(route('admin.posts.update', $post), [
        'title' => 'New Title',
        'slug' => 'new-title',
        'content' => 'Updated content.',
        'status' => 'published',
    ])->assertRedirect(route('admin.posts.index'));

    expect($post->fresh()->title)->toBe('New Title');
});

test('updating post syncs file references', function () {
    $post = Post::factory()->create();
    $post->files()->attach(File::factory()->count(3)->create()->pluck('id'));

    $newFiles = File::factory()->count(2)->create();

    $this->patch(route('admin.posts.update', $post), [
        'title' => $post->title,
        'slug' => $post->slug,
        'content' => $post->content,
        'status' => $post->status,
        'image_uuids' => $newFiles->pluck('uuid')->all(),
    ])->assertRedirect(route('admin.posts.index'));

    expect($post->files()->count())->toBe(2);
});

test('post can be deleted', function () {
    $post = Post::factory()->create();

    $this->delete(route('admin.posts.destroy', $post))
        ->assertRedirect(route('admin.posts.index'));

    expect(Post::find($post->id))->toBeNull();
    expect(Post::withTrashed()->find($post->id))->not->toBeNull();
});

test('unauthenticated users cannot access posts', function () {
    auth()->logout();

    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
});
