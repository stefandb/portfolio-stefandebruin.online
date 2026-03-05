<?php

use App\Models\PageVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it tracks a page visit', function () {
    $response = $this->get('/', [
        'referer' => 'https://google.com',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('page_visits', [
        'url' => route('home'),
        'referer' => 'https://google.com',
    ]);

    $visit = PageVisit::first();
    expect($visit->session_id)->not->toBeNull();
});

test('it does not track non-GET requests', function () {
    // Er is geen POST route op /, maar we kunnen een willekeurige POST proberen
    // Of we mocken de middleware. Maar laten we een bestaande route proberen of gewoon POST /
    $response = $this->post('/');

    // Afhankelijk van of de route bestaat, kan dit 405 of iets anders zijn
    // Het belangrijkste is dat er GEEN PageVisit wordt aangemaakt
    $this->assertDatabaseEmpty('page_visits');
});

test('it does not track inertia partial reloads', function () {
    $response = $this->get('/', [
        'X-Inertia-Partial-Data' => 'some-component',
    ]);

    $this->assertDatabaseEmpty('page_visits');
});

test('it does not track authenticated users', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertStatus(200);
    $this->assertDatabaseEmpty('page_visits');
});

test('it does not track excluded ip addresses', function () {
    config(['app.tracking_excluded_ips' => '127.0.0.1,192.168.1.1']);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])->get('/');

    $response->assertStatus(200);
    $this->assertDatabaseEmpty('page_visits');

    $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])->get('/');

    $this->assertDatabaseEmpty('page_visits');

    $response = $this->withServerVariables(['REMOTE_ADDR' => '1.1.1.1'])->get('/');

    $this->assertDatabaseCount('page_visits', 1);
});
