<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowServiceWorkerRootScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('sw.js') || $request->is('build/sw.js')) {
            $response->headers->set('Service-Worker-Allowed', '/');
            $response->headers->set('Cache-Control', 'no-cache');
        }

        return $response;
    }
}
