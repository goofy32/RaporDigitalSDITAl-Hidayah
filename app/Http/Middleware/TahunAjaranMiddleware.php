<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TahunAjaran;

class TahunAjaranMiddleware
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
        // Ambil parameter untuk menampilkan tahun ajaran terarsipkan
        $tampilkanArsip = $request->has('showArchived');
        
        // Cek jika ada tahun ajaran yang dipilih di session
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Jika tidak ada di session, gunakan tahun ajaran aktif
        if (!$tahunAjaranId) {
            $activeTahunAjaran = TahunAjaran::where('is_active', true)->first();
            
            if ($activeTahunAjaran) {
                session(['tahun_ajaran_id' => $activeTahunAjaran->id]);
                $tahunAjaranId = $activeTahunAjaran->id;
            } else {
                // Jika tidak ada tahun ajaran aktif, gunakan tahun ajaran terbaru
                $latestTahunAjaran = TahunAjaran::orderBy('tanggal_mulai', 'desc')->first();
                if ($latestTahunAjaran) {
                    session(['tahun_ajaran_id' => $latestTahunAjaran->id]);
                    $tahunAjaranId = $latestTahunAjaran->id;
                }
            }
        }
        
        // Share tahun ajaran ke semua view
    $tahunAjaran = null;
    if ($tahunAjaranId) {
        // Gunakan withTrashed() untuk mendapatkan tahun ajaran meskipun telah diarsipkan
        $tahunAjaran = TahunAjaran::withTrashed()->find($tahunAjaranId);
        
        if ($tahunAjaran) {
            view()->share('activeTahunAjaran', $tahunAjaran);
            
            // Tambahkan tahun ajaran ke request untuk digunakan di controller
            $request->merge(['tahun_ajaran_id' => $tahunAjaranId]);
            
            // Tambahkan ke request attributes agar bisa diakses dengan $request->attributes->get('tahun_ajaran_id')
            $request->attributes->add(['tahun_ajaran_id' => $tahunAjaranId]);
            
            // Tambahkan flag untuk mengetahui apakah tahun ajaran yang dipilih telah diarsipkan
            $request->attributes->add(['tahun_ajaran_is_archived' => $tahunAjaran->trashed()]);
            view()->share('tahunAjaranIsArchived', $tahunAjaran->trashed());
        } else {
            // Tahun ajaran tidak ditemukan, mungkin sudah dihapus
            // Reset session dan cari tahun ajaran lain
            session()->forget('tahun_ajaran_id');
            $newActiveTahunAjaran = TahunAjaran::where('is_active', true)->first();
            
            if ($newActiveTahunAjaran) {
                session(['tahun_ajaran_id' => $newActiveTahunAjaran->id]);
                view()->share('activeTahunAjaran', $newActiveTahunAjaran);
                $request->merge(['tahun_ajaran_id' => $newActiveTahunAjaran->id]);
                $request->attributes->add(['tahun_ajaran_id' => $newActiveTahunAjaran->id]);
                $request->attributes->add(['tahun_ajaran_is_archived' => $newActiveTahunAjaran->trashed()]);
                view()->share('tahunAjaranIsArchived', $newActiveTahunAjaran->trashed());
            }
        }
    }
        
        // Ambil daftar semua tahun ajaran (untuk dropdown selector)
        $tahunAjaransQuery = TahunAjaran::orderBy('is_active', 'desc')
                                   ->orderBy('tanggal_mulai', 'desc');
                               
        // Jika tampilkanArsip true, sertakan tahun ajaran yang telah diarsipkan
        if ($tampilkanArsip) {
            $tahunAjaransQuery->withTrashed();
        }
        
        $tahunAjarans = $tahunAjaransQuery->get();
        
        view()->share('tahunAjarans', $tahunAjarans);
        view()->share('tampilkanArsip', $tampilkanArsip);
        
        // Pastikan field tahun_ajaran_id otomatis terisi saat form submission
        if ($request->isMethod('post') || $request->isMethod('put')) {
            if (!$request->has('tahun_ajaran_id') && $tahunAjaranId) {
                $request->merge(['tahun_ajaran_id' => $tahunAjaranId]);
            }
        }
        
        // Tampilkan peringatan jika menggunakan tahun ajaran yang diarsipkan
        if ($tahunAjaran && $tahunAjaran->trashed()) {
            // Gunakan session flash untuk menampilkan peringatan
            session()->flash('warning', 'Anda sedang melihat data untuk tahun ajaran yang diarsipkan. Beberapa fitur mungkin terbatas.');
        }
        
        return $next($request);
    }
}