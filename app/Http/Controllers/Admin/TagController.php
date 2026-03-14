<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Tags\Tag;

class TagController extends Controller
{
    public function index(Request $request): Response
    {
        $tags = Tag::query()
            ->when($request->input('search'), function ($query, $search): void {
                $query->whereRaw("name->>'en' ILIKE ?", ["%{$search}%"]);
            })
            ->orderBy('name->en')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Tags/Create');
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        Tag::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return redirect()->route('admin.tags.index')->with('success', 'Tag succesvol aangemaakt.');
    }

    public function edit(Tag $tag): Response
    {
        return Inertia::render('Admin/Tags/Edit', [
            'tag' => $tag,
        ]);
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->setTranslation('name', 'en', $request->name);
        $tag->type = $request->type;
        $tag->save();

        return redirect()->route('admin.tags.index')->with('success', 'Tag succesvol bijgewerkt.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->route('admin.tags.index')->with('success', 'Tag succesvol verwijderd.');
    }
}
