<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
});

// ─── Index ───────────────────────────────────────────────────────────────────

test('files index returns paginated json', function () {
    $user = User::factory()->create();
    File::factory()->count(5)->create();

    actingAs($user)
        ->getJson(route('admin.files.index'))
        ->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => ['uuid', 'name', 'original_name', 'url', 'mime_type', 'size', 'is_image', 'created_at'],
            ],
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['path', 'per_page', 'next_cursor', 'prev_cursor'],
        ]);
});

test('files index requires authentication', function () {
    $this->getJson(route('admin.files.index'))->assertUnauthorized();
});

// ─── Store ────────────────────────────────────────────────────────────────────

test('files can be uploaded', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg', 100, 100);

    $response = actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => [$file]]);

    $response
        ->assertCreated()
        ->assertJsonCount(1)
        ->assertJsonPath('0.original_name', 'photo.jpg')
        ->assertJsonPath('0.is_image', true);

    expect(File::count())->toBe(1);

    $stored = File::first();
    Storage::disk('public')->assertExists($stored->path);
});

test('multiple files can be uploaded at once', function () {
    $user = User::factory()->create();
    $files = [
        UploadedFile::fake()->image('a.jpg'),
        UploadedFile::fake()->image('b.png'),
        UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
    ];

    actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => $files])
        ->assertCreated()
        ->assertJsonCount(3);

    expect(File::count())->toBe(3);
});

test('upload requires at least one file', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => []])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('files');
});

test('upload file size is limited to 20mb', function () {
    $user = User::factory()->create();
    $oversized = UploadedFile::fake()->create('big.jpg', 21_000, 'image/jpeg');

    actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => [$oversized]])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('files.0');
});

test('upload requires authentication', function () {
    $file = UploadedFile::fake()->image('photo.jpg');

    $this->postJson(route('admin.files.store'), ['files' => [$file]])->assertUnauthorized();
});

// ─── Destroy ─────────────────────────────────────────────────────────────────

test('files can be deleted', function () {
    $user = User::factory()->create();
    $uploadedFile = UploadedFile::fake()->image('photo.jpg');

    Storage::disk('public')->put('files/test/photo.jpg', $uploadedFile->getContent());

    $file = File::factory()->create([
        'path' => 'files/test/photo.jpg',
        'disk' => 'public',
    ]);

    actingAs($user)
        ->deleteJson(route('admin.files.destroy', $file->uuid))
        ->assertNoContent();

    expect(File::find($file->id))->toBeNull();
    Storage::disk('public')->assertMissing('files/test/photo.jpg');
});

test('delete requires authentication', function () {
    $file = File::factory()->create();

    $this->deleteJson(route('admin.files.destroy', $file->uuid))->assertUnauthorized();
});

test('file resource includes correct structure', function () {
    $user = User::factory()->create();
    $imageFile = File::factory()->image()->create();

    actingAs($user)
        ->getJson(route('admin.files.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $imageFile->uuid)
        ->assertJsonPath('data.0.is_image', true);
});
