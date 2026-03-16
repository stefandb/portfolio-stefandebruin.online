<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ProcessImageVariants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFileRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $files = File::query()
            ->latest()
            ->cursorPaginate(24);

        return FileResource::collection($files);
    }

    public function store(StoreFileRequest $request, ProcessImageVariants $processor): JsonResponse
    {
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $uuid = (string) Str::uuid();
            $path = $file->store("files/{$uuid}", 'public');

            $fileModel = File::create([
                'uuid' => $uuid,
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            $processor->handle($fileModel);

            $uploaded[] = $fileModel->fresh();
        }

        return response()->json(FileResource::collection($uploaded), 201);
    }

    public function destroy(File $file): JsonResponse
    {
        $pathsToDelete = array_filter([
            $file->path,
            $file->webp_path,
            $file->thumbnail_path,
            $file->og_path,
            ...array_values($file->responsive_paths ?? []),
        ]);

        Storage::disk($file->disk)->delete($pathsToDelete);

        $file->delete();

        return response()->json(null, 204);
    }
}
