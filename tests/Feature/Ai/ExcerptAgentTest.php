<?php

use App\Ai\Agents\ExcerptSummaryAgent;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('excerpt is generated from plain text content', function () {
    ExcerptSummaryAgent::fake();

    $user = User::factory()->create();

    actingAs($user)
        ->postJson(route('ai.excerpt'), [
            'text' => 'This is a project about building a portfolio website with Laravel and Vue.',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['excerpt'])
        ->assertJsonIsObject();

    ExcerptSummaryAgent::assertPrompted(fn ($prompt) => $prompt->contains('portfolio website'));
});

test('html tags are stripped before prompting the agent', function () {
    ExcerptSummaryAgent::fake();

    $user = User::factory()->create();

    actingAs($user)
        ->postJson(route('ai.excerpt'), [
            'text' => '<h1>My Project</h1><p>Built with <strong>Laravel</strong> and TinyMCE.</p>',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['excerpt']);

    ExcerptSummaryAgent::assertPrompted(fn ($prompt) => $prompt->contains('My Project')
        && $prompt->contains('Laravel')
        && ! $prompt->contains('<h1>')
        && ! $prompt->contains('<strong>'));
});

test('response excerpt is a string', function () {
    ExcerptSummaryAgent::fake();

    $user = User::factory()->create();

    $response = actingAs($user)
        ->postJson(route('ai.excerpt'), [
            'text' => 'A detailed project description about building modern web applications.',
        ])
        ->assertSuccessful()
        ->json();

    expect($response['excerpt'])->toBeString()->not->toBeEmpty();
});

test('excerpt endpoint requires authentication', function () {
    $this->postJson(route('ai.excerpt'), ['text' => 'Some content'])->assertUnauthorized();
});

test('excerpt endpoint requires text input', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson(route('ai.excerpt'), [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('text');
});

test('excerpt endpoint requires minimum text length', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson(route('ai.excerpt'), ['text' => 'Short'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('text');
});
