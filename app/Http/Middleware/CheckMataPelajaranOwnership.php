<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\MataPelajaran;

class CheckMataPelajaranOwnership
{
    public function handle(Request $request, Closure $next)
    {
        $mataPelajaranId = $request->route('mata_pelajaran_id');
        $guru = auth()->guard('guru')->user();

        if (!$mataPelajaranId || !$guru) {
            return redirect()->route('login');
        }

        $mataPelajaran = MataPelajaran::where('id', $mataPelajaranId)
            ->where('guru_id', $guru->id)
            ->first();

        if (!$mataPelajaran) {
            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}