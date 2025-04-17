<?php

namespace App\Http\Controllers;

use App\Models\KkmSetting;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KkmController extends Controller
{
    /**
     * Menampilkan view untuk setting KKM
     */
    public function edit($mataPelajaranId)
    {
        $mataPelajaran = MataPelajaran::with(['kelas'])->findOrFail($mataPelajaranId);
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $kkmSetting = KkmSetting::getForMataPelajaran($mataPelajaranId, $tahunAjaranId);
        
        return view('admin.kkm.edit', compact('mataPelajaran', 'kkmSetting'));
    }
    
    /**
     * Update setting KKM untuk mata pelajaran tertentu
     */
    public function update(Request $request, $mataPelajaranId)
    {
        $request->validate([
            'nilai_kkm' => 'required|numeric|between:0,100',
            'bobot_tp' => 'required|numeric|min:0|max:10',
            'bobot_lm' => 'required|numeric|min:0|max:10',
            'bobot_as' => 'required|numeric|min:0|max:10',
            'keterangan' => 'nullable|string|max:500'
        ]);
        
        $mataPelajaran = MataPelajaran::findOrFail($mataPelajaranId);
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $kkmSetting = KkmSetting::updateOrCreate(
            [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tahun_ajaran_id' => $tahunAjaranId
            ],
            [
                'nilai_kkm' => $request->nilai_kkm,
                'bobot_tp' => $request->bobot_tp,
                'bobot_lm' => $request->bobot_lm,
                'bobot_as' => $request->bobot_as,
                'keterangan' => $request->keterangan
            ]
        );
        
        return redirect()->route('subject.index')
            ->with('success', 'Setting KKM untuk mata pelajaran '.$mataPelajaran->nama_pelajaran.' berhasil diperbarui');
    }
    
    /**
     * Menampilkan view untuk setting KKM oleh guru
     */
    public function editByTeacher($mataPelajaranId)
    {
        $guru = Auth::guard('guru')->user();
        
        $mataPelajaran = MataPelajaran::with(['kelas'])
            ->where('guru_id', $guru->id)
            ->findOrFail($mataPelajaranId);
        
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $kkmSetting = KkmSetting::getForMataPelajaran($mataPelajaranId, $tahunAjaranId);
        
        return view('pengajar.kkm.edit', compact('mataPelajaran', 'kkmSetting'));
    }
    
    /**
     * Update setting KKM untuk mata pelajaran tertentu oleh guru
     */
    public function updateByTeacher(Request $request, $mataPelajaranId)
    {
        $request->validate([
            'nilai_kkm' => 'required|numeric|between:0,100',
            'bobot_tp' => 'required|numeric|min:0|max:10',
            'bobot_lm' => 'required|numeric|min:0|max:10',
            'bobot_as' => 'required|numeric|min:0|max:10',
            'keterangan' => 'nullable|string|max:500'
        ]);
        
        $guru = Auth::guard('guru')->user();
        
        $mataPelajaran = MataPelajaran::where('guru_id', $guru->id)
            ->findOrFail($mataPelajaranId);
        
        $tahunAjaranId = session('tahun_ajaran_id');
        
        $kkmSetting = KkmSetting::updateOrCreate(
            [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tahun_ajaran_id' => $tahunAjaranId
            ],
            [
                'nilai_kkm' => $request->nilai_kkm,
                'bobot_tp' => $request->bobot_tp,
                'bobot_lm' => $request->bobot_lm,
                'bobot_as' => $request->bobot_as,
                'keterangan' => $request->keterangan
            ]
        );
        
        return redirect()->route('pengajar.subject.index')
            ->with('success', 'Setting KKM untuk mata pelajaran '.$mataPelajaran->nama_pelajaran.' berhasil diperbarui');
    }
    
    /**
     * Get KKM settings via AJAX
     */
    public function getKkmSettings($mataPelajaranId)
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        $kkmSetting = KkmSetting::getForMataPelajaran($mataPelajaranId, $tahunAjaranId);
        
        return response()->json([
            'success' => true,
            'data' => $kkmSetting
        ]);
    }
}