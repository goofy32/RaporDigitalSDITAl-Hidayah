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
            [
                'placeholder_key' => '${nama_siswa}',
                'description' => 'Nama lengkap siswa',
                'category' => 'siswa',
                'sample_value' => 'John Doe',
                'is_required' => true
            ],
            [
                'placeholder_key' => '${nisn}',
                'description' => 'NISN siswa',
                'category' => 'siswa',
                'sample_value' => '1234567890',
                'is_required' => true
            ],
            // Nilai Mata Pelajaran
            [
                'placeholder_key' => '${nilai_matematika}',
                'description' => 'Nilai mata pelajaran matematika',
                'category' => 'nilai',
                'sample_value' => '85',
                'is_required' => true
            ],
            // Kehadiran
            [
                'placeholder_key' => '${sakit}',
                'description' => 'Jumlah hari tidak hadir karena sakit',
                'category' => 'absensi',
                'sample_value' => '3',
                'is_required' => true
            ],
            // Dan seterusnya...
        ];

        foreach ($placeholders as $placeholder) {
            ReportPlaceholder::create($placeholder);
        }
    }
}