<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminIpAllowlist
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = config('admin.allowed_ips');

        // If no allowlist is configured, allow all IPs
        if (empty($allowed)) {
            return $next($request);
        }

        if (!in_array($request->ip(), $allowed, true)) {
            abort(403, 'Access denied.');
        }

        return $next($request);
    }
}
