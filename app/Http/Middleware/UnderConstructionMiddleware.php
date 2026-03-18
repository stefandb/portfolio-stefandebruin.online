<?php

namespace App\Http\Middleware;
use Closure;use Illuminate\Http\Request;use Inertia\Inertia;use Inertia\Response;

class UnderConstructionMiddleware{
    public function handle(Request $request, Closure $next)
    {
        if (!config('app.under_construction') || app()->environment('local') || auth()->check() || $request->has('preview')) {
            return $next($request);
        }

        return Inertia::render('UnderConstruction')
            ->toResponse($request)
            ->setStatusCode(503)
            ->header('Retry-After', 3600);
    }
}
