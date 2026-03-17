<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Redirect;

class PostObserver
{
    public function updated(Post $post): void
    {
        if (! $post->wasChanged('slug')) {
            return;
        }

        $oldSlug = $post->getOriginal('slug');
        $newSlug = $post->slug;

        Redirect::create([
            'from' => "/posts/{$oldSlug}",
            'to' => "/posts/{$newSlug}",
            'status_code' => 301,
        ]);
    }
}
