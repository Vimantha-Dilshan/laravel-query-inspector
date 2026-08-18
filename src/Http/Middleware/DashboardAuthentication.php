<?php

declare(strict_types=1);

namespace Inspector\QueryMonitor\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        // In local development, always allow access.
        if (app()->environment('local')) {
            return $next($request);
        }

        $gate = config('query-monitor.dashboard.gate');

        // When no gate is configured, allow any authenticated user.
        if ($gate === null) {
            if (auth()->check()) {
                return $next($request);
            }

            abort(403, 'You must be authenticated to access the Query Monitor dashboard.');
        }

        if (! auth()->check() || ! auth()->user()?->can($gate)) {
            abort(403, 'You do not have permission to access the Query Monitor dashboard.');
        }

        return $next($request);
    }
}
