<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\BobotNilai;
use Illuminate\Http\Request;

class BobotNilaiController extends Controller
{
    public function index()
    {
        // Alihkan ke dashboard dengan pesan
        return redirect()->route('admin.dashboard')
            ->with('info', 'Pengaturan Bobot Nilai tersedia melalui menu pengaturan di navbar');
    }
    
    public function subjectView()
    {
        return view('admin.subject.bobot-nilai');
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
            return response()->json([
                'success' => false,
                'message' => 'Total bobot harus 100% (1.0)'
            ], 422);
        }
        
        // Ambil nilai bobot lama untuk logging
        $bobotNilai = BobotNilai::getDefault();
        $oldValues = [
            'bobot_tp' => $bobotNilai->bobot_tp,
            'bobot_lm' => $bobotNilai->bobot_lm,
            'bobot_as' => $bobotNilai->bobot_as
        ];
        
        // Update bobot nilai
        $bobotNilai->update($validated);
        
        // Log perubahan untuk audit
        $user = auth()->user();
        Log::info('Bobot nilai diperbarui', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'old_values' => $oldValues,
            'new_values' => $validated,
            'timestamp' => now()->toDateTimeString()
        ]);
        
        // Tambahkan ke AuditLog jika model tersedia
        if (class_exists('App\Models\AuditLog')) {
            \App\Models\AuditLog::create([
                'user_type' => get_class($user),
                'user_id' => $user->id,
                'action' => 'update',
                'model_type' => 'App\Models\BobotNilai',
                'model_id' => $bobotNilai->id,
                'description' => 'Perubahan bobot nilai',
                'old_values' => $oldValues,
                'new_values' => $validated,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Bobot nilai berhasil diperbarui!'
        ]);
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