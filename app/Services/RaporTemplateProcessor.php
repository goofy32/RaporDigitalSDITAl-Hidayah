<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\Siswa;
use App\Models\ReportPlaceholder;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Exception;

class RaporTemplateProcessor 
{
    protected $processor;
    protected $template;
    protected $siswa;
    protected $type;
    protected $placeholders;

    public function __construct(ReportTemplate $template, Siswa $siswa, $type = 'UTS') 
    {
        $this->template = $template;
        $this->siswa = $siswa;
        $this->type = $type;
    
        // Validasi template path tidak kosong
        if (empty($template->path)) {
            throw new \Exception("Path template kosong");
        }
    
        // Log untuk debugging
        \Log::info('Template Info:', [
            'template_id' => $template->id,
            'filename' => $template->filename,
            'path' => $template->path,
            'is_active' => $template->is_active
        ]);
    
        // Pastikan path template adalah file yang valid
        $templatePath = storage_path('app/public/' . $template->path);
        
        // Log path lengkap
        \Log::info('Full template path:', [
            'path' => $templatePath,
            'exists' => file_exists($templatePath),
            'is_file' => is_file($templatePath)
        ]);
    
        if (!file_exists($templatePath)) {
            throw new \Exception("Template file tidak ditemukan: {$templatePath}");
        }
    
        if (!is_file($templatePath)) {
            throw new \Exception("Path bukan merupakan file yang valid: {$templatePath}");
        }
    
        try {
            $this->processor = new TemplateProcessor($templatePath);
        } catch (\Exception $e) {
            throw new \Exception("Gagal memproses template: " . $e->getMessage());
        }
    
        $this->placeholders = ReportPlaceholder::all()->groupBy('category');
    }
    protected function collectAllData()
    {
        // Data Siswa
        $data = [
            'nama_siswa' => $this->siswa->nama,
            'nisn' => $this->siswa->nisn,
            'nis' => $this->siswa->nis,
            'kelas' => $this->siswa->kelas->nama_kelas,
            'tahun_ajaran' => $this->siswa->kelas->tahun_ajaran,
        ];

        // Data Nilai
        $nilaiQuery = $this->siswa->nilais()
            ->with(['mataPelajaran'])
            ->whereHas('mataPelajaran', function($q) {
                $q->where('semester', $this->type === 'UTS' ? 1 : 2);
            });
            
        $nilai = $nilaiQuery->get()->groupBy('mataPelajaran.nama_pelajaran');
        
        // Pengumpulan nilai per mata pelajaran
        $mapelKeys = [
            'pai' => 'Pendidikan Agama Islam',
            'ppkn' => 'PPKN',
            'bahasa_indonesia' => 'Bahasa Indonesia',
            'matematika' => 'Matematika',
            'pjok' => 'PJOK',
            'seni_musik' => 'Seni Musik',
            'bahasa_inggris' => 'Bahasa Inggris'
        ];

        foreach ($mapelKeys as $key => $nama) {
            if (isset($nilai[$nama])) {
                $nilaiMapel = $nilai[$nama];
                $avgNilai = $nilaiMapel->avg('nilai_akhir_rapor');
                $data["nilai_$key"] = number_format($avgNilai, 1);
                $data["capaian_$key"] = $nilaiMapel->first()->deskripsi ?? '-';
            } else {
                $data["nilai_$key"] = '-';
                $data["capaian_$key"] = '-';
            }
        }

        // Muatan Lokal (dinamis)
        $mulok = $nilai->filter(function($value, $key) {
            return str_contains(strtolower($key), 'mulok');
        });

        $mulokCount = 1;
        foreach ($mulok as $nama => $nilaiMulok) {
            if ($mulokCount <= 5) {
                $data["nama_mulok$mulokCount"] = $nama;
                $data["nilai_mulok$mulokCount"] = number_format($nilaiMulok->avg('nilai_akhir_rapor'), 1);
                $data["capaian_mulok$mulokCount"] = $nilaiMulok->first()->deskripsi ?? '-';
                $mulokCount++;
            }
        }

        // Data Ekstrakurikuler
        $ekstrakurikuler = $this->siswa->nilaiEkstrakurikuler()
            ->with('ekstrakurikuler')
            ->get();
            
        for ($i = 1; $i <= 6; $i++) {
            if (isset($ekstrakurikuler[$i-1])) {
                $ekskul = $ekstrakurikuler[$i-1];
                $data["ekskul{$i}_nama"] = $ekskul->ekstrakurikuler->nama_ekstrakurikuler;
                $data["ekskul{$i}_keterangan"] = $ekskul->deskripsi;
            } else {
                $data["ekskul{$i}_nama"] = '-';
                $data["ekskul{$i}_keterangan"] = '-';
            }
        }

        // Data Kehadiran
        $absensi = $this->siswa->absensi;
        if ($absensi) {
            $data['sakit'] = $absensi->sakit;
            $data['izin'] = $absensi->izin;
            $data['tanpa_keterangan'] = $absensi->tanpa_keterangan;
        } else {
            $data['sakit'] = 0;
            $data['izin'] = 0;
            $data['tanpa_keterangan'] = 0;
        }

        // Validasi data wajib
        foreach ($this->placeholders as $category => $placeholders) {
            foreach ($placeholders as $placeholder) {
                if ($placeholder->is_required && !isset($data[$placeholder->placeholder_key])) {
                    throw new Exception("Data wajib {$placeholder->description} belum tersedia");
                }
            }
        }

        return $data;
    }

    public function generate()
    {
        try {
            // 1. Validasi data
            $this->validateData();

            // 2. Kumpulkan dan isi data
            $data = $this->collectAllData();
            foreach ($data as $key => $value) {
                $this->processor->setValue($key, $value ?: '-');
            }

            // 3. Generate file
            $filename = $this->generateFilename();
            $outputPath = $this->saveFile($filename);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => "generated/{$filename}"
            ];

        } catch (Exception $e) {
            throw new Exception('Gagal generate rapor: ' . $e->getMessage());
        }
    }

    protected function validateData()
    {
        // Cek nilai akademik
        $hasNilai = $this->siswa->nilais()
            ->whereHas('mataPelajaran', function($q) {
                $q->where('semester', $this->type === 'UTS' ? 1 : 2);
            })->exists();

        if (!$hasNilai) {
            throw new Exception('Data nilai akademik belum lengkap');
        }

        // Cek kehadiran
        if (!$this->siswa->absensi) {
            throw new Exception('Data kehadiran belum lengkap');
        }
    }

    protected function generateFilename()
    {
        return sprintf(
            "rapor_%s_%s_%s_%s.docx",
            $this->type,
            $this->siswa->nis,
            str_replace(' ', '_', $this->siswa->kelas->nama_kelas),
            time()
        );
    }

    protected function saveFile($filename)
    {
        $outputPath = storage_path("app/public/generated/{$filename}");
        
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }
        
        $this->processor->saveAs($outputPath);
        return "generated/{$filename}";
    }
}