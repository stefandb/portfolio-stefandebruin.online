<?php

namespace App\Http\Controllers\Admin;

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

    public function store(StoreFileRequest $request): JsonResponse
    {
        $uploaded = [];

        foreach ($request->file('files') as $file) {
            $uuid = (string) Str::uuid();
            $path = $file->store("files/{$uuid}", 'public');

            $uploaded[] = File::create([
                'uuid' => $uuid,
                'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return response()->json(FileResource::collection($uploaded), 201);
    }

    public function destroy(File $file): JsonResponse
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        return response()->json(null, 204);
    }
}
