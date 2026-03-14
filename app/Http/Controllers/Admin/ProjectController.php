<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProjectRequest;
use App\Http\Requests\Admin\UpdateProjectRequest;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\Tag;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $projects = Project::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            })
            ->when($request->input('status'), function ($query, $status) {
                if ($status !== 'all') {
                    $query->where('status', $status);
                }
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Create', [
            'availableTags' => Tag::query()->orderBy('name->en')->get()->pluck('name')->filter()->values()->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $project = Project::create($request->validated());

        if ($request->has('tags')) {
            $project->attachTags($request->tags);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $project->addMedia($image)->toMediaCollection('images');
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project succesvol aangemaakt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Show', [
            'project' => $project->load(['tags', 'media']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Edit', [
            'project' => $project->load(['tags', 'media']),
            'availableTags' => Tag::query()->orderBy('name->en')->get()->pluck('name')->filter()->values()->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $project->update($request->validated());

        if ($request->has('tags')) {
            $project->syncTags($request->tags);
        }

        if ($request->filled('deleted_media')) {
            Media::query()
                ->where('model_type', Project::class)
                ->where('model_id', $project->id)
                ->whereIn('id', $request->deleted_media)
                ->each(fn (Media $media) => $media->delete());
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $project->addMedia($image)->toMediaCollection('images');
            }
        }

        return redirect()->route('admin.projects.index')->with('success', 'Project succesvol bijgewerkt.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Project succesvol verwijderd.');
    }
}
