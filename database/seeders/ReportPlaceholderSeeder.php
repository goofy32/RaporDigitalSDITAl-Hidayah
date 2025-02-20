<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportPlaceholder;

class ReportPlaceholderSeeder extends Seeder
{
    public function run()
    {
        $placeholders = [
            // Data Siswa
            ['placeholder_key' => 'nama_siswa', 'description' => 'Nama lengkap siswa', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'nisn', 'description' => 'NISN', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'nis', 'description' => 'NIS', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'kelas', 'description' => 'Kelas', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'tahun_ajaran', 'description' => 'Tahun Ajaran', 'category' => 'siswa', 'is_required' => true],
            
            // Nilai PAI
            ['placeholder_key' => 'nilai_pai', 'description' => 'Nilai PAI', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_pai', 'description' => 'Capaian PAI', 'category' => 'nilai', 'is_required' => false],
            
            // Nilai PPKN
            ['placeholder_key' => 'nilai_ppkn', 'description' => 'Nilai PPKN', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_ppkn', 'description' => 'Capaian PPKN', 'category' => 'nilai', 'is_required' => false],
            
            // Nilai Bahasa Indonesia
            ['placeholder_key' => 'nilai_bahasa_indonesia', 'description' => 'Nilai Bahasa Indonesia', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_bahasa_indonesia', 'description' => 'Capaian Bahasa Indonesia', 'category' => 'nilai', 'is_required' => false],
            
            // Nilai Matematika
            ['placeholder_key' => 'nilai_matematika', 'description' => 'Nilai Matematika', 'category' => 'nilai', 'is_required' => true],
            ['placeholder_key' => 'capaian_matematika', 'description' => 'Capaian Matematika', 'category' => 'nilai', 'is_required' => false],
            
            // Nilai PJOK
            ['placeholder_key' => 'nilai_pjok', 'description' => 'Nilai PJOK', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_pjok', 'description' => 'Capaian PJOK', 'category' => 'nilai', 'is_required' => false],

            // Nilai Seni
            ['placeholder_key' => 'nilai_seni_musik', 'description' => 'Nilai Seni Musik', 'category' => 'nilai', 'is_required' => false],
            
            // Nilai Bahasa Inggris
            ['placeholder_key' => 'nilai_bahasa_inggris', 'description' => 'Nilai Bahasa Inggris', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_bahasa_inggris', 'description' => 'Capaian Bahasa Inggris', 'category' => 'nilai', 'is_required' => false],
            
            // Muatan Lokal
            ['placeholder_key' => 'nilai_mulok1', 'description' => 'Nilai Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_mulok1', 'description' => 'Capaian Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'nilai_mulok2', 'description' => 'Nilai Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_mulok2', 'description' => 'Capaian Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'nilai_mulok3', 'description' => 'Nilai Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_mulok3', 'description' => 'Capaian Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'nilai_mulok4', 'description' => 'Nilai Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_mulok4', 'description' => 'Capaian Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'nilai_mulok5', 'description' => 'Nilai Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],
            ['placeholder_key' => 'capaian_mulok5', 'description' => 'Capaian Muatan Lokal 1', 'category' => 'nilai', 'is_required' => false],

            // Ekstrakurikuler
            ['placeholder_key' => 'ekskul1_nama', 'description' => 'Nama Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul1_keterangan', 'description' => 'Keterangan Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul2_nama', 'description' => 'Nama Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul2_keterangan', 'description' => 'Keterangan Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul3_nama', 'description' => 'Nama Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul3_keterangan', 'description' => 'Keterangan Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul4_nama', 'description' => 'Nama Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul4_keterangan', 'description' => 'Keterangan Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul5_nama', 'description' => 'Nama Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul5_keterangan', 'description' => 'Keterangan Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul6_nama', 'description' => 'Nama Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            ['placeholder_key' => 'ekskul6_keterangan', 'description' => 'Keterangan Ekstrakurikuler 1', 'category' => 'ekskul', 'is_required' => false],
            // Tambahkan ekskul 2-5 dengan pola yang sama
            
            // Kehadiran
            ['placeholder_key' => 'sakit', 'description' => 'Jumlah Sakit', 'category' => 'kehadiran', 'is_required' => true],
            ['placeholder_key' => 'izin', 'description' => 'Jumlah Izin', 'category' => 'kehadiran', 'is_required' => false],
            ['placeholder_key' => 'tanpa_keterangan', 'description' => 'Jumlah Tanpa Keterangan', 'category' => 'kehadiran', 'is_required' => false],
            
            // Lainnya
            ['placeholder_key' => 'catatan_guru', 'description' => 'Catatan Guru', 'category' => 'lainnya', 'is_required' => false],
            ['placeholder_key' => 'nomor_telepon', 'description' => 'Nomor Telepon Sekolah', 'category' => 'sekolah', 'is_required' => false],
        ];

        foreach ($placeholders as $placeholder) {
            ReportPlaceholder::create($placeholder);
        }
    }
}