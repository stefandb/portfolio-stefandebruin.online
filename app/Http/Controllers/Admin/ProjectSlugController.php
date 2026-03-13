<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectSlugController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $slug = $request->string('slug')->toString();
        $excludeId = $request->integer('exclude_id') ?: null;

        $isTaken = Project::query()
            ->where('slug', $slug)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        if (! $isTaken) {
            return response()->json(['available' => true]);
        }

        $suffix = 1;

        do {
            $candidate = "{$slug}-{$suffix}";
            $candidateIsTaken = Project::query()
                ->where('slug', $candidate)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists();
            $suffix++;
        } while ($candidateIsTaken);

        return response()->json(['available' => false, 'suggested' => $candidate]);
    }
}
