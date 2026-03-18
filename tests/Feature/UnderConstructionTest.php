<?php

use App\Models\User;

beforeEach(function () {
    config()->set('app.under_construction', true);

    Route::post('/some-endpoint', fn () => response()->noContent())->middleware('web');
});

it('returns 503 for guests when under construction', function () {
    $response = $this->get('/');

    $response->assertStatus(503);
    $response->assertHeader('Retry-After', '3600');
    $response->assertInertia(fn ($page) => $page->component('UnderConstruction')
    );
});

it('allows authenticated users through', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
});

it('allows preview param', function () {
    $response = $this->get('/?preview=1');

    $response->assertStatus(200);
});

it('returns normal response when disabled', function () {
    config()->set('app.under_construction', false);

    $response = $this->get('/');

    $response->assertStatus(200);
});

it('returns inertia page for GET requests', function () {
    $response = $this->get('/');

    $response->assertStatus(503);
    $response->assertInertia(fn ($page) => $page->component('UnderConstruction')
    );
});

it('returns json for post requests expecting json', function () {
    $response = $this->post('/some-endpoint', [], [
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(503);
    $response->assertJson([
        'message' => 'Service temporarily unavailable.',
    ]);
});

it('returns empty 503 for normal post requests', function () {
    $response = $this->post('/some-endpoint');

    $response->assertStatus(503);
    $response->assertContent('');
});
