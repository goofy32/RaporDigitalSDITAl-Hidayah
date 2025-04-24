<?php

namespace App\Http\Controllers;

use App\Models\Kkm;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class KkmController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('mataPelajarans')->get();
        $kkms = Kkm::with(['mataPelajaran', 'kelas'])->get();
        
        return view('admin.kkm.index', compact('kelas', 'kkms'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'nilai' => 'required|integer|min:0|max:100',
        ]);
        
        $validated['tahun_ajaran_id'] = session('tahun_ajaran_id');
        
        $mataPelajaran = MataPelajaran::find($request->mata_pelajaran_id);
        $validated['kelas_id'] = $mataPelajaran->kelas_id;
        
        Kkm::updateOrCreate(
            [
                'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
                'tahun_ajaran_id' => $validated['tahun_ajaran_id']
            ],
            $validated
        );
        
        return redirect()->route('admin.kkm.index')->with('success', 'KKM berhasil disimpan!');
    }
    
    public function getKkm($mapelId)
    {
        $kkm = Kkm::where('mata_pelajaran_id', $mapelId)
               ->where('tahun_ajaran_id', session('tahun_ajaran_id'))
               ->first();
               
        return response()->json(['kkm' => $kkm ? $kkm->nilai : 70]);
    }
}