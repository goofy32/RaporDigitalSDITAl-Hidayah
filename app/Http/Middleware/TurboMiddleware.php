<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TurboMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only modify responses for Turbo requests
        if ($request->hasHeader('Turbo-Frame') || $request->hasHeader('X-Turbo-Frame')) {
            // Get the Turbo Frame ID
            $frameId = $request->header('Turbo-Frame') ?: $request->header('X-Turbo-Frame');
            
            if ($frameId === 'main') {
                // For main frame requests, we want to preserve the sidebar
                // by marking it as a permanent element
                
                // Check if the response is HTML
                if (is_object($response) && method_exists($response, 'header') && 
                    strpos($response->header('Content-Type'), 'text/html') !== false) {
                    
                    // Add a custom header to indicate this is a Turbo frame response
                    $response->header('Turbo-Frame-Response', 'true');
                }
            }
        }

        return $response;
    }
}