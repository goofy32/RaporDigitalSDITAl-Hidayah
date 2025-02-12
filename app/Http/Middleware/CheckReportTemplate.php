<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ReportTemplate;

class CheckReportTemplate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $type = $request->route('type') ?? 'UTS';
        
        if (!ReportTemplate::where('type', $type)->where('is_active', true)->exists()) {
            return redirect()->back()->with('error', 'Template rapor belum tersedia');
        }

        return $next($request);
    }
}