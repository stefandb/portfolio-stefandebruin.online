<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\File;
use App\Models\Post;
use App\Models\PostSerie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = Post::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Posts/Create', [
            'availableSeries' => PostSerie::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $post = Post::create($request->safe()->except(['image_uuids']));

        if ($request->filled('image_uuids')) {
            $fileIds = File::query()->whereIn('uuid', $request->image_uuids)->pluck('id');
            $post->files()->sync($fileIds);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Bericht succesvol aangemaakt.');
    }

    public function edit(Post $post): Response
    {
        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post->load(['serie', 'files']),
            'availableSeries' => PostSerie::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $post->update($request->safe()->except(['image_uuids']));

        $fileIds = File::query()->whereIn('uuid', $request->image_uuids ?? [])->pluck('id');
        $post->files()->sync($fileIds);

        return redirect()->route('admin.posts.index')->with('success', 'Bericht succesvol bijgewerkt.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Bericht succesvol verwijderd.');
    }
}
