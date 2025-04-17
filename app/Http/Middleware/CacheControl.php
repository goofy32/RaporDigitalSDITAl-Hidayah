<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CacheControl
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Jika response adalah jenis khusus, gunakan headers collection
        if ($response instanceof StreamedResponse || $response instanceof BinaryFileResponse) {
            // Untuk StreamedResponse dan BinaryFileResponse, gunakan koleksi headers
            
            // Cache gambar icon dengan aggressive caching
            if ($request->is('images/icons/*')) {
                $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
                $response->headers->set('X-Content-Type-Options', 'nosniff');
                $response->headers->set('X-Frame-Options', 'DENY');
            }
            // Cache asset statis
            else if ($request->is('*.css', '*.js')) {
                $response->headers->set('Cache-Control', 'public, max-age=2592000, immutable');
                $response->headers->set('X-Content-Type-Options', 'nosniff');
            }
            // Prevent caching untuk konten dinamis
            else {
                $response->headers->set('Cache-Control', 'no-store, private, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');
            }
        } 
        else {
            // Response reguler, gunakan metode header()
            
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
        
        return $response;
    }
}