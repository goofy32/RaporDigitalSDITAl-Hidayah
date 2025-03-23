<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;
use App\Models\ProfilSekolah;
use Carbon\Carbon;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil data tahun ajaran dari profil sekolah jika ada
        $profil = ProfilSekolah::first();
        $tahunAjaranText = $profil ? $profil->tahun_pelajaran : '2024/2025';
        $semester = $profil ? $profil->semester : 1;

        // Buat tahun ajaran saat ini
        $tahunAjaran = TahunAjaran::create([
            'tahun_ajaran' => $tahunAjaranText,
            'is_active' => true,
            'tanggal_mulai' => Carbon::now()->startOfYear(),
            'tanggal_selesai' => Carbon::now()->endOfYear(),
            'semester' => $semester,
            'deskripsi' => 'Tahun Ajaran ' . $tahunAjaranText . ' Semester ' . ($semester == 1 ? 'Ganjil' : 'Genap')
        ]);

        // Tambahkan satu tahun ajaran lagi untuk contoh
        $nextYear = explode('/', $tahunAjaranText);
        if (count($nextYear) == 2) {
            $nextYearStart = (int)$nextYear[0] + 1;
            $nextYearEnd = (int)$nextYear[1] + 1;
            $nextTahunAjaran = $nextYearStart . '/' . $nextYearEnd;

            TahunAjaran::create([
                'tahun_ajaran' => $nextTahunAjaran,
                'is_active' => false,
                'tanggal_mulai' => Carbon::now()->addYear()->startOfYear(),
                'tanggal_selesai' => Carbon::now()->addYear()->endOfYear(),
                'semester' => 1,
                'deskripsi' => 'Tahun Ajaran ' . $nextTahunAjaran . ' Semester Ganjil'
            ]);
        }

        // Migrasi data yang sudah ada ke model tahun ajaran baru
        if ($tahunAjaran) {
            // Update kelas
            \DB::table('kelas')->update(['tahun_ajaran_id' => $tahunAjaran->id]);
            
            // Update mata pelajaran
            \DB::table('mata_pelajarans')->update(['tahun_ajaran_id' => $tahunAjaran->id]);
            
            // Update template rapor
            \DB::table('report_templates')
                ->where('tahun_ajaran', $tahunAjaranText)
                ->orWhereNull('tahun_ajaran')
                ->update([
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'tahun_ajaran_text' => $tahunAjaranText
                ]);
                
            // Update report generations
            \DB::table('report_generations')
                ->where('tahun_ajaran', $tahunAjaranText)
                ->orWhereNull('tahun_ajaran')
                ->update(['tahun_ajaran_id' => $tahunAjaran->id]);
        }
    }
}