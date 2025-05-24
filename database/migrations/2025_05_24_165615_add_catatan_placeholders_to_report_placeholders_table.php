<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\ReportPlaceholder;

return new class extends Migration
{
    public function up()
    {
        // Placeholder untuk catatan mata pelajaran (1-10 untuk konsistensi)
        $placeholders = [
            [
                'placeholder_key' => 'catatan_guru',
                'description' => 'Catatan umum dari wali kelas untuk siswa',
                'category' => 'catatan',
                'sample_value' => 'Siswa menunjukkan perkembangan yang baik dalam belajar.',
                'is_required' => false
            ]
        ];

        // Tambahkan placeholder catatan mata pelajaran 1-10
        for ($i = 1; $i <= 10; $i++) {
            $placeholders[] = [
                'placeholder_key' => "catatan_matapelajaran{$i}",
                'description' => "Catatan untuk mata pelajaran ke-{$i}",
                'category' => 'catatan_mapel',
                'sample_value' => "Catatan untuk mata pelajaran {$i}",
                'is_required' => false
            ];
        }

        foreach ($placeholders as $placeholder) {
            ReportPlaceholder::updateOrCreate(
                ['placeholder_key' => $placeholder['placeholder_key']],
                $placeholder
            );
        }
    }

    public function down()
    {
        $placeholders = ['catatan_guru'];
        
        for ($i = 1; $i <= 10; $i++) {
            $placeholders[] = "catatan_matapelajaran{$i}";
        }

        ReportPlaceholder::whereIn('placeholder_key', $placeholders)->delete();
    }
};