<?php

namespace App\Http\Controllers;

use App\Models\BobotNilai;
use Illuminate\Http\Request;

class BobotNilaiController extends Controller
{
    public function index()
    {
        $bobotNilai = BobotNilai::getDefault();
        
        return view('admin.bobot_nilai.index', compact('bobotNilai'));
    }
    
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bobot_tp' => 'required|numeric|min:0|max:1',
            'bobot_lm' => 'required|numeric|min:0|max:1',
            'bobot_as' => 'required|numeric|min:0|max:1',
        ]);
        
        // Pastikan total bobot adalah 1 (100%)
        $total = $validated['bobot_tp'] + $validated['bobot_lm'] + $validated['bobot_as'];
        if (round($total, 2) != 1) {
            return redirect()->back()->with('error', 'Total bobot harus 100% (1.0)');
        }
        
        $bobotNilai = BobotNilai::getDefault();
        $bobotNilai->update($validated);
        
        return redirect()->route('admin.bobot_nilai.index')->with('success', 'Bobot nilai berhasil diperbarui!');
    }
    
    public function getBobot()
    {
        $bobot = BobotNilai::getDefault();
        
        return response()->json([
            'bobot_tp' => $bobot->bobot_tp,
            'bobot_lm' => $bobot->bobot_lm,
            'bobot_as' => $bobot->bobot_as
        ]);
    }
}