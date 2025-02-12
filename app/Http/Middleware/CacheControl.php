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
    
        // Cache gambar icon dengan aggressive caching
        if ($request->is('images/icons/*')) {
            return $response
                ->header('Cache-Control', 'public, max-age=31536000, immutable')
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('X-Frame-Options', 'DENY');
        }
    
        // Cache asset statis
        if ($request->is('*.css', '*.js')) {
            return $response
                ->header('Cache-Control', 'public, max-age=2592000, immutable')
                ->header('X-Content-Type-Options', 'nosniff');
        }
    
        // Prevent caching untuk konten dinamis
        return $response
            ->header('Cache-Control', 'no-store, private, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}