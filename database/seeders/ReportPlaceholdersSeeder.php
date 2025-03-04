<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportPlaceholdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus placeholder lama yang sudah tidak digunakan
        DB::table('report_placeholders')->truncate();
        
        // Set placeholder dasar (yang tidak terkait mata pelajaran)
        $basicPlaceholders = [
            ['placeholder_key' => 'nama_siswa', 'description' => 'Nama lengkap siswa', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'nisn', 'description' => 'NISN siswa', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'nis', 'description' => 'NIS siswa', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'kelas', 'description' => 'Kelas siswa', 'category' => 'siswa', 'is_required' => true],
            ['placeholder_key' => 'tahun_ajaran', 'description' => 'Tahun ajaran', 'category' => 'sekolah', 'is_required' => true],
            ['placeholder_key' => 'nomor_telepon', 'description' => 'Nomor telepon sekolah', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'kepala_sekolah', 'description' => 'Nama kepala sekolah', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'wali_kelas', 'description' => 'Nama wali kelas', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'nip_kepala_sekolah', 'description' => 'NIP kepala sekolah', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'nip_wali_kelas', 'description' => 'NIP wali kelas', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'tanggal_terbit', 'description' => 'Tanggal terbit rapor', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'tempat_terbit', 'description' => 'Tempat terbit rapor', 'category' => 'sekolah', 'is_required' => false],
            ['placeholder_key' => 'sakit', 'description' => 'Jumlah hari sakit', 'category' => 'kehadiran', 'is_required' => true],
            ['placeholder_key' => 'izin', 'description' => 'Jumlah hari izin', 'category' => 'kehadiran', 'is_required' => true],
            ['placeholder_key' => 'tanpa_keterangan', 'description' => 'Jumlah hari tanpa keterangan', 'category' => 'kehadiran', 'is_required' => true],
            ['placeholder_key' => 'catatan_guru', 'description' => 'Catatan dari guru', 'category' => 'lainnya', 'is_required' => false],
        ];
        
        DB::table('report_placeholders')->insert($basicPlaceholders);
        
        // Set placeholder dinamis untuk mata pelajaran
        $mapelPlaceholders = [];
        for ($i = 1; $i <= 10; $i++) {
            $mapelPlaceholders[] = [
                'placeholder_key' => "nama_matapelajaran{$i}",
                'description' => "Nama mata pelajaran {$i}",
                'category' => 'mapel',
                'is_required' => $i <= 4 // hanya 4 mata pelajaran pertama yang wajib
            ];
            
            $mapelPlaceholders[] = [
                'placeholder_key' => "nilai_matapelajaran{$i}",
                'description' => "Nilai mata pelajaran {$i}",
                'category' => 'mapel',
                'is_required' => $i <= 4
            ];
            
            $mapelPlaceholders[] = [
                'placeholder_key' => "capaian_matapelajaran{$i}",
                'description' => "Capaian mata pelajaran {$i}",
                'category' => 'mapel',
                'is_required' => $i <= 4
            ];
        }
        
        DB::table('report_placeholders')->insert($mapelPlaceholders);
        
        // Set placeholder untuk muatan lokal
        $mulokPlaceholders = [];
        for ($i = 1; $i <= 5; $i++) {
            $mulokPlaceholders[] = [
                'placeholder_key' => "nama_mulok{$i}",
                'description' => "Nama muatan lokal {$i}",
                'category' => 'mulok',
                'is_required' => false
            ];
            
            $mulokPlaceholders[] = [
                'placeholder_key' => "nilai_mulok{$i}",
                'description' => "Nilai muatan lokal {$i}",
                'category' => 'mulok',
                'is_required' => false
            ];
            
            $mulokPlaceholders[] = [
                'placeholder_key' => "capaian_mulok{$i}",
                'description' => "Capaian muatan lokal {$i}",
                'category' => 'mulok',
                'is_required' => false
            ];
        }
        
        DB::table('report_placeholders')->insert($mulokPlaceholders);
        
        // Set placeholder untuk ekstrakurikuler
        $ekskulPlaceholders = [];
        for ($i = 1; $i <= 5; $i++) {
            $ekskulPlaceholders[] = [
                'placeholder_key' => "ekskul{$i}_nama",
                'description' => "Nama ekstrakurikuler {$i}",
                'category' => 'ekskul',
                'is_required' => false
            ];
            
            $ekskulPlaceholders[] = [
                'placeholder_key' => "ekskul{$i}_keterangan",
                'description' => "Keterangan ekstrakurikuler {$i}",
                'category' => 'ekskul',
                'is_required' => false
            ];
        }
        
        DB::table('report_placeholders')->insert($ekskulPlaceholders);
        
        // Support juga untuk placeholder lama (backward compatibility)
        $oldPlaceholders = [
            ['placeholder_key' => 'nilai_pai', 'description' => 'Nilai Pendidikan Agama Islam', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_pai', 'description' => 'Capaian Pendidikan Agama Islam', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'nilai_ppkn', 'description' => 'Nilai PPKN', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_ppkn', 'description' => 'Capaian PPKN', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'nilai_bahasa_indonesia', 'description' => 'Nilai Bahasa Indonesia', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_bahasa_indonesia', 'description' => 'Capaian Bahasa Indonesia', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'nilai_matematika', 'description' => 'Nilai Matematika', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_matematika', 'description' => 'Capaian Matematika', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'nilai_pjok', 'description' => 'Nilai PJOK', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_pjok', 'description' => 'Capaian PJOK', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'nilai_seni_musik', 'description' => 'Nilai Seni Musik', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_seni_musik', 'description' => 'Capaian Seni Musik', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'nilai_bahasa_inggris', 'description' => 'Nilai Bahasa Inggris', 'category' => 'mapel_lama', 'is_required' => false],
            ['placeholder_key' => 'capaian_bahasa_inggris', 'description' => 'Capaian Bahasa Inggris', 'category' => 'mapel_lama', 'is_required' => false],
        ];
        
        DB::table('report_placeholders')->insert($oldPlaceholders);
    }
}