<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Redirect;

class ProjectObserver
{
    public function updated(Project $project): void
    {
        if (! $project->wasChanged('slug')) {
            return;
        }

        $oldSlug = $project->getOriginal('slug');
        $newSlug = $project->slug;

        Redirect::create([
            'from' => "/projects/{$oldSlug}",
            'to' => "/projects/{$newSlug}",
            'status_code' => 301,
        ]);
    }
}
