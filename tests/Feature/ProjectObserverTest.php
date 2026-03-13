<?php

use App\Models\Project;
use App\Models\Redirect;

test('a redirect is created when a project slug changes', function () {
    $project = Project::factory()->create(['slug' => 'old-slug']);

    $project->update(['slug' => 'new-slug']);

    expect(Redirect::where('from', '/projects/old-slug')->where('to', '/projects/new-slug')->exists())->toBeTrue();
});

test('no redirect is created when slug does not change', function () {
    $project = Project::factory()->create(['slug' => 'my-slug']);

    $project->update(['title' => 'Updated Title']);

    expect(Redirect::count())->toBe(0);
});

test('redirect has 301 status code', function () {
    $project = Project::factory()->create(['slug' => 'old-slug']);

    $project->update(['slug' => 'new-slug']);

    $redirect = Redirect::first();

    expect($redirect->status_code)->toBe(301);
});
