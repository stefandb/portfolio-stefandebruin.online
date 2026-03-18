<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class UnderConstructionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.under_construction')) {
            return $next($request);
        }

        // ✅ Bypass rules
        if (
            auth()->check() ||
            app()->environment('local') ||
            $request->has('preview')
        ) {
            return $next($request);
        }

        // 🔥 GET / HEAD → toon pagina
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            $response = Inertia::render('UnderConstruction')
                ->toResponse($request)
                ->setStatusCode(503);

            $response->headers->set('Retry-After', '3600');

            return $response;
        }

        if ($request->header('X-Inertia')) {
            return response()->json([
                'message' => 'Service temporarily unavailable.',
            ], 503);
        }

        // 🔥 Non-GET → API/Forms correct afhandelen
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Service temporarily unavailable.',
            ], 503)->header('Retry-After', '3600');
        }

        // 🔥 Fallback (bijv. form POST zonder JSON)
        return response('', 503)
            ->header('Retry-After', '3600');
    }
}
