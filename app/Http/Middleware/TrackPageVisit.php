<?php

namespace App\Http\Middleware;

use App\Models\PageVisit;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageVisit
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            PageVisit::create([
                'url' => $request->fullUrl(),
                'referer' => $request->header('referer'),
                'session_id' => $request->session()->getId(),
                'trackable_id' => $this->getTrackableId($request),
                'trackable_type' => $this->getTrackableType($request),
            ]);
        }

        return $response;
    }

    protected function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        if ($request->header('X-Inertia-Partial-Data')) {
            return false;
        }

        if ($request->user()) {
            return false;
        }

        $excludedIps = explode(',', config('app.tracking_excluded_ips', ''));

        if (in_array($request->ip(), array_filter($excludedIps))) {
            return false;
        }

        return true;
    }

    protected function getTrackableId(Request $request): mixed
    {
        return null;
    }

    protected function getTrackableType(Request $request): ?string
    {
        return null;
    }
}
