<?php

use App\Models\File;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
});

// ─── Index ────────────────────────────────────────────────────────────────────

test('files index returns paginated json', function () {
    $user = User::factory()->create();
    File::factory()->count(5)->create();

    actingAs($user)
        ->getJson(route('admin.files.index'))
        ->assertSuccessful()
        ->assertJsonCount(5, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'uuid', 'name', 'original_name', 'url',
                    'webp_url', 'thumbnail_url', 'og_url', 'responsive_urls',
                    'mime_type', 'size', 'is_image', 'created_at',
                ],
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
    $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

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
        UploadedFile::fake()->image('a.jpg', 800, 600),
        UploadedFile::fake()->image('b.png', 800, 600),
        UploadedFile::fake()->create('document.pdf', 500, 'application/pdf'),
    ];

    actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => $files])
        ->assertCreated()
        ->assertJsonCount(3);

    expect(File::count())->toBe(3);
});

test('image variants are generated on upload', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('photo.jpg', 1400, 900);

    $response = actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => [$file]])
        ->assertCreated();

    $stored = File::first();

    expect($stored->webp_path)->not->toBeNull()
        ->and($stored->thumbnail_path)->not->toBeNull()
        ->and($stored->og_path)->not->toBeNull()
        ->and($stored->responsive_paths)->not->toBeNull();

    Storage::disk('public')->assertExists($stored->webp_path);
    Storage::disk('public')->assertExists($stored->thumbnail_path);
    Storage::disk('public')->assertExists($stored->og_path);

    foreach ($stored->responsive_paths as $path) {
        Storage::disk('public')->assertExists($path);
    }

    // Responsive widths ≤ original 1400px → 480, 768, 1200 all generated
    expect($stored->responsive_paths)->toHaveKeys([480, 768, 1200]);
});

test('responsive variants skip widths larger than the original', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->image('small.jpg', 600, 400);

    actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => [$file]])
        ->assertCreated();

    $stored = File::first();

    // Only widths ≤ 600px should be generated (480 yes, 768 no, 1200 no)
    expect($stored->responsive_paths)->toHaveKey(480)
        ->and($stored->responsive_paths)->not->toHaveKey(768)
        ->and($stored->responsive_paths)->not->toHaveKey(1200);
});

test('non-image files do not generate variants', function () {
    $user = User::factory()->create();
    $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

    actingAs($user)
        ->postJson(route('admin.files.store'), ['files' => [$file]])
        ->assertCreated();

    $stored = File::first();

    expect($stored->webp_path)->toBeNull()
        ->and($stored->thumbnail_path)->toBeNull()
        ->and($stored->og_path)->toBeNull()
        ->and($stored->responsive_paths)->toBeNull();
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

test('deleting a file removes all variants from storage', function () {
    $user = User::factory()->create();

    foreach (['files/test/photo.jpg', 'files/test/photo.webp', 'files/test/photo-thumb.webp', 'files/test/photo-og.webp', 'files/test/photo-480w.webp'] as $path) {
        Storage::disk('public')->put($path, 'data');
    }

    $file = File::factory()->create([
        'path' => 'files/test/photo.jpg',
        'disk' => 'public',
        'webp_path' => 'files/test/photo.webp',
        'thumbnail_path' => 'files/test/photo-thumb.webp',
        'og_path' => 'files/test/photo-og.webp',
        'responsive_paths' => [480 => 'files/test/photo-480w.webp'],
    ]);

    actingAs($user)
        ->deleteJson(route('admin.files.destroy', $file->uuid))
        ->assertNoContent();

    expect(File::find($file->id))->toBeNull();

    foreach (['files/test/photo.jpg', 'files/test/photo.webp', 'files/test/photo-thumb.webp', 'files/test/photo-og.webp', 'files/test/photo-480w.webp'] as $path) {
        Storage::disk('public')->assertMissing($path);
    }
});

test('delete requires authentication', function () {
    $file = File::factory()->create();

    $this->deleteJson(route('admin.files.destroy', $file->uuid))->assertUnauthorized();
});

// ─── Resource structure ───────────────────────────────────────────────────────

test('file resource exposes variant urls', function () {
    $user = User::factory()->create();
    $imageFile = File::factory()->image()->create([
        'webp_path' => 'files/test/photo.webp',
        'thumbnail_path' => 'files/test/photo-thumb.webp',
        'og_path' => 'files/test/photo-og.webp',
        'responsive_paths' => [480 => 'files/test/photo-480w.webp'],
    ]);

    actingAs($user)
        ->getJson(route('admin.files.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.0.uuid', $imageFile->uuid)
        ->assertJsonPath('data.0.is_image', true)
        ->assertJsonStructure([
            'data' => [['webp_url', 'thumbnail_url', 'og_url', 'responsive_urls']],
        ]);
});
