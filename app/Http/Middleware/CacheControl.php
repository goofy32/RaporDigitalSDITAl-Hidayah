<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheControl
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('*.jpg', '*.jpeg', '*.png', '*.gif', '*.svg', '*.ico')) {
            return $response->header('Cache-Control', 'public, max-age=31536000');
        }

        if ($request->is('*.css', '*.js')) {
            return $response->header('Cache-Control', 'public, max-age=2592000');
        }

        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }
}