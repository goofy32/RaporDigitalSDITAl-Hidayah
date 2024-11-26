<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\Store;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeout
{
    protected $session;
    protected $timeout;

    public function __construct(Store $session)
    {
        $this->session = $session;
        $this->timeout = config('session.lifetime') * 60;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $lastActivity = $this->session->get('last_activity', time());
        
        if (time() - $lastActivity > $this->timeout) {
            Auth::logout();
            $this->session->flush();
            
            if ($request->wantsJson() || $request->hasHeader('Turbo-Frame')) {
                return response()->json(['message' => 'Session expired'], 401);
            }
            
            return redirect()->route('login')->with('message', 'Sesi Anda telah berakhir.');
        }

        $this->session->put('last_activity', time());
        return $next($request);
    }
}