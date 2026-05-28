<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Conditionally applies Sanctum authentication based on the
 * vmstats.api_auth_required config value, read at request time.
 *
 * Used so we can flip the env var to require auth on the mutating
 * API routes without rebuilding the route cache.
 */
class ApiAuthIfEnabled
{
    public function __construct(protected Authenticate $authenticate) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('vmstats.api_auth_required')) {
            return $next($request);
        }

        return $this->authenticate->handle($request, $next, 'sanctum');
    }
}
