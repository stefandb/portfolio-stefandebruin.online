<?php

namespace App\Http\Controllers\Ai;

use App\Ai\Agents\ExcerptSummaryAgent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\GenerateExcerptRequest;
use Illuminate\Http\JsonResponse;
use Throwable;

class ExcerptController extends Controller
{
    public function __invoke(GenerateExcerptRequest $request): JsonResponse
    {
        try {
            $plainText = strip_tags($request->text);

            $response = (new ExcerptSummaryAgent)->prompt(
                "Generate an excerpt for this project:\n\n{$plainText}"
            );

            /** @var array{excerpt: string} $response */
            return response()->json(['excerpt' => $response['excerpt']]);
        } catch (Throwable) {
            return response()->json(
                ['message' => 'Failed to generate excerpt. Please try again.'],
                500,
            );
        }
    }
}
