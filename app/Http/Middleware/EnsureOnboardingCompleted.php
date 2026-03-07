<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingCompleted
{
    use ApiResponse;

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()->hasCompletedOnboarding()) {
            return $this->error('Onboarding not completed.', 403);
        }

        return $next($request);
    }
}
