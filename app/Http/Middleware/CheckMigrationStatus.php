<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMigrationStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $failedMarker = config('constants.migration.failed_marker');

        // The upgrade dialog waits for the health endpoint before it reloads.
        if ($request->is('api/health', 'api/v1/health') || ! is_file($failedMarker)) {
            return $next($request);
        }

        $error = file_get_contents($failedMarker);

        if ($request->is('api/*') || $request->expectsJson()) {
            return response()->json(['message' => "Database migration failed: {$error}"], 503);
        }

        return response()->view('errors.migration-failed', ['error' => $error], 503);
    }
}
