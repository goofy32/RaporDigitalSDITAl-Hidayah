<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplyTahunAjaranFilter
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
        // Get tahun ajaran from session
        $tahunAjaranId = session('tahun_ajaran_id');
        $selectedSemester = session('selected_semester');
        
        if ($tahunAjaranId) {
            // Save tahun ajaran ID as global variable in request
            $request->merge([
                'tahun_ajaran_id' => $tahunAjaranId,
                'selected_semester' => $selectedSemester
            ]);
            
            // Share tahun ajaran ID to all views
            view()->share('selectedTahunAjaranId', $tahunAjaranId);
            view()->share('selectedSemester', $selectedSemester);
        }

        // Process request
        $response = $next($request);

        return $response;
    }
}