<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\Siswa;
use App\Models\ReportPlaceholder;
use App\Models\ProfilSekolah;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class RaporTemplateProcessor 
{
    // Constants for error types
    const ERROR_TEMPLATE_MISSING = 'template_missing';
    const ERROR_TEMPLATE_INVALID = 'template_invalid';
    const ERROR_DATA_INCOMPLETE = 'data_incomplete';
    const ERROR_PLACEHOLDER_MISSING = 'placeholder_missing';
    const ERROR_FILE_PROCESSING = 'file_processing';

    protected $processor;
    protected $template;
    protected $siswa;
    protected $type;
    protected $placeholders;
    protected $schoolProfile;

    public function __construct(ReportTemplate $template, Siswa $siswa, $type = 'UTS') 
    {
        $this->template = $template;
        $this->siswa = $siswa;
        $this->type = $type;
        $this->schoolProfile = ProfilSekolah::first();
    
        // Validasi template path tidak kosong
        if (empty($template->path)) {
            throw new Exception(
                "Path template kosong. Hubungi admin untuk upload template baru.",
                self::ERROR_TEMPLATE_MISSING
            );
        }
    
        // Log untuk debugging
        Log::info('Template Info:', [
            'template_id' => $template->id,
            'filename' => $template->filename,
            'path' => $template->path,
            'is_active' => $template->is_active
        ]);
    
        // Pastikan path template adalah file yang valid
        $templatePath = storage_path('app/public/' . $template->path);
        
        // Log path lengkap
        Log::info('Full template path:', [
            'path' => $templatePath,
            'exists' => file_exists($templatePath),
            'is_file' => is_file($templatePath)
        ]);
    
        if (!file_exists($templatePath)) {
            throw new Exception(
                "Template file tidak ditemukan: {$templatePath}. Hubungi admin untuk upload template baru.",
                self::ERROR_TEMPLATE_MISSING
            );
        }
    
        if (!is_file($templatePath)) {
            throw new Exception(
                "Path bukan merupakan file yang valid: {$templatePath}. Hubungi admin untuk upload template baru.",
                self::ERROR_TEMPLATE_INVALID
            );
        }
    
        try {
            $this->processor = new TemplateProcessor($templatePath);
        } catch (\Exception $e) {
            throw new Exception(
                "Gagal memproses template: " . $e->getMessage() . ". Hubungi admin untuk perbaiki template.",
                self::ERROR_TEMPLATE_INVALID
            );
        }
    
        $this->placeholders = ReportPlaceholder::all()->groupBy('category');
    }

    /**
     * Mengumpulkan semua data yang diperlukan untuk template rapor
     * 
     * @return array
     */
    protected function collectAllData()
    {
        $semester = $this->type === 'UTS' ? 1 : 2;
        
        // Data Siswa
        $data = [
            'nama_siswa' => $this->siswa->nama,
            'nisn' => $this->siswa->nisn ?: '-',
            'nis' => $this->siswa->nis ?: '-',
            'kelas' => $this->siswa->kelas->nomor_kelas . ' ' . $this->siswa->kelas->nama_kelas,
            'tahun_ajaran' => $this->siswa->kelas->tahun_ajaran ?: ($this->schoolProfile->tahun_pelajaran ?? '-'),
        ];

        // Data Nilai
        $nilaiQuery = $this->siswa->nilais()
            ->with(['mataPelajaran'])
            ->whereHas('mataPelajaran', function($q) use ($semester) {
                $q->where('semester', $semester);
            });
            
        $nilaiCollection = $nilaiQuery->get();
        
        // Grouping by mata pelajaran
        $nilai = $nilaiCollection->groupBy('mataPelajaran.nama_pelajaran');
        
        Log::info('Data nilai yang diambil:', [
            'siswa_id' => $this->siswa->id,
            'mapel_count' => $nilai->count(),
            'mapel_list' => $nilai->keys()->toArray()
        ]);
        
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

        // Pisahkan mata pelajaran reguler dan muatan lokal
        $mapelReguler = $nilaiCollection->groupBy('mataPelajaran.nama_pelajaran')
            ->filter(function($value, $key) {
                return $value->first() && $value->first()->mataPelajaran && 
                       $value->first()->mataPelajaran->is_muatan_lokal == 0;
            });
            
        $mulok = $nilaiCollection->groupBy('mataPelajaran.nama_pelajaran')
            ->filter(function($value, $key) {
                return $value->first() && $value->first()->mataPelajaran && 
                       $value->first()->mataPelajaran->is_muatan_lokal == 1;
            });
            
        Log::info('Pembagian mata pelajaran:', [
            'reguler_count' => $mapelReguler->count(),
            'mulok_count' => $mulok->count(),
            'reguler_list' => $mapelReguler->keys()->toArray(),
            'mulok_list' => $mulok->keys()->toArray()
        ]);

        // Ambil nilai untuk mata pelajaran reguler sesuai definisi mapelKeys
        foreach ($mapelKeys as $key => $nama) {
            // Coba cari mata pelajaran yang memiliki nama yang mirip
            $matchedMapel = null;
            
            foreach ($mapelReguler as $mapelName => $nilaiMapel) {
                // Cek apakah nama mapel mengandung kata kunci (misalnya "Matematika", "Bahasa Indonesia", dll)
                if (stripos($mapelName, $nama) !== false || 
                    similar_text(strtolower($mapelName), strtolower($nama)) > 0.7 * strlen($nama)) {
                    $matchedMapel = $nilaiMapel;
                    break;
                }
            }
            
            if ($matchedMapel) {
                // Cari nilai akhir rapor
                $nilaiAkhir = $matchedMapel->where('nilai_akhir_rapor', '!=', null)->first();
                
                if ($nilaiAkhir) {
                    $nilaiValue = $nilaiAkhir->nilai_akhir_rapor;
                    $data["nilai_$key"] = number_format($nilaiValue, 1);
                    $data["capaian_$key"] = $nilaiAkhir->deskripsi ?? 
                        $this->generateSpecificCapaian($nilaiValue, $key);
                } else {
                    // Jika tidak ada nilai_akhir_rapor, gunakan rata-rata nilai lain
                    $avgNilai = $matchedMapel->avg('nilai_tp');
                    if ($avgNilai) {
                        $data["nilai_$key"] = number_format($avgNilai, 1);
                        $data["capaian_$key"] = $this->generateSpecificCapaian($avgNilai, $key);
                    } else {
                        $data["nilai_$key"] = '-';
                        $data["capaian_$key"] = '-';
                    }
                }
            } else {
                // Jika tidak menemukan mapel yang sesuai
                $data["nilai_$key"] = '-';
                $data["capaian_$key"] = '-';
            }
        }

        // Muatan Lokal
        $mulokCount = 1;
        foreach ($mulok as $nama => $nilaiMulok) {
            if ($mulokCount <= 5) {
                $nilaiAkhir = $nilaiMulok->where('nilai_akhir_rapor', '!=', null)->first();
                
                // Nama muatan lokal
                $data["nama_mulok$mulokCount"] = $nama;
                
                if ($nilaiAkhir) {
                    $nilaiValue = $nilaiAkhir->nilai_akhir_rapor;
                    $data["nilai_mulok$mulokCount"] = number_format($nilaiValue, 1);
                    $data["capaian_mulok$mulokCount"] = $nilaiAkhir->deskripsi ?? 
                        $this->generateCapaianDeskripsi($nilaiValue, $nama);
                } else {
                    // Jika tidak ada nilai_akhir_rapor, cari alternatif
                    $avgNilai = $nilaiMulok->avg('nilai_tp');
                    $data["nilai_mulok$mulokCount"] = $avgNilai ? number_format($avgNilai, 1) : '-';
                    $data["capaian_mulok$mulokCount"] = $avgNilai ? 
                        $this->generateCapaianDeskripsi($avgNilai, $nama) : '-';
                }
                
                $mulokCount++;
            }
        }

        // Tambahkan default untuk muatan lokal yang tidak ada
        for ($i = $mulokCount; $i <= 5; $i++) {
            $data["nama_mulok$i"] = '-';
            $data["nilai_mulok$i"] = '-';
            $data["capaian_mulok$i"] = '-';
        }

        // Data Ekstrakurikuler
        $ekstrakurikuler = $this->siswa->nilaiEkstrakurikuler()
            ->with('ekstrakurikuler')
            ->get();
            
        for ($i = 1; $i <= 6; $i++) {
            if (isset($ekstrakurikuler[$i-1])) {
                $ekskul = $ekstrakurikuler[$i-1];
                $data["ekskul{$i}_nama"] = $ekskul->ekstrakurikuler->nama_ekstrakurikuler ?? '-';
                $data["ekskul{$i}_keterangan"] = $ekskul->deskripsi ?: '-';
            } else {
                $data["ekskul{$i}_nama"] = '-';
                $data["ekskul{$i}_keterangan"] = '-';
            }
        }

        // Data Kehadiran
        $absensi = $this->siswa->absensi()->where('semester', $semester)->first();
        if ($absensi) {
            $data['sakit'] = $absensi->sakit ?: '0';
            $data['izin'] = $absensi->izin ?: '0';
            $data['tanpa_keterangan'] = $absensi->tanpa_keterangan ?: '0';
        } else {
            $data['sakit'] = '0';
            $data['izin'] = '0';
            $data['tanpa_keterangan'] = '0';
        }
        
        // Data sekolah
        if ($this->schoolProfile) {
            $data['nomor_telepon'] = $this->schoolProfile->telepon ?: '-';
            $data['kepala_sekolah'] = $this->schoolProfile->kepala_sekolah ?: '-';
            $data['wali_kelas'] = $this->siswa->kelas->waliKelasName ?: '-';
            $data['nip_kepala_sekolah'] = $this->schoolProfile->nip_kepala_sekolah ?? '-';
            $data['nip_wali_kelas'] = '-'; // Tambahkan jika ada
            $data['tanggal_terbit'] = date('d-m-Y');
            $data['tempat_terbit'] = $this->schoolProfile->tempat_terbit ?: '-';
        } else {
            $data['nomor_telepon'] = '-';
            $data['kepala_sekolah'] = '-';
            $data['wali_kelas'] = '-';
            $data['tanggal_terbit'] = date('d-m-Y');
            $data['tempat_terbit'] = '-';
        }
        
        // Catatan guru (isi default jika tidak ada)
        $data['catatan_guru'] = '-';

        return $data;
    }
    
    /**
     * Generate deskripsi capaian otomatis berdasarkan nilai
     *
     * @param float $nilai Nilai siswa
     * @param string $namaMapel Nama mata pelajaran
     * @param string|null $namaSiswa Nama siswa (opsional)
     * @return string Deskripsi capaian
     */
    protected function generateCapaianDeskripsi($nilai, $namaMapel, $namaSiswa = null)
    {
        // Default nama siswa jika tidak disediakan
        $siswa = $namaSiswa ?? $this->siswa->nama ?? 'Siswa';
        
        // Kategori berdasarkan nilai
        if ($nilai >= 90) {
            return "{$siswa} menunjukkan penguasaan yang sangat baik dalam mata pelajaran {$namaMapel}. Mampu memahami konsep, menerapkan, dan menganalisis dengan sangat baik.";
        } elseif ($nilai >= 80) {
            return "{$siswa} menunjukkan penguasaan yang baik dalam mata pelajaran {$namaMapel}. Mampu memahami konsep dan menerapkannya dengan baik.";
        } elseif ($nilai >= 70) {
            return "{$siswa} menunjukkan penguasaan yang cukup dalam mata pelajaran {$namaMapel}. Sudah mampu memahami konsep dasar dengan baik.";
        } elseif ($nilai >= 60) {
            return "{$siswa} menunjukkan penguasaan yang sedang dalam mata pelajaran {$namaMapel}. Perlu meningkatkan pemahaman konsep dasar.";
        } else {
            return "{$siswa} perlu bimbingan lebih lanjut dalam mata pelajaran {$namaMapel}. Disarankan untuk mengulang pembelajaran materi dasar.";
        }
    }

    /**
     * Generate deskripsi capaian spesifik berdasarkan mata pelajaran
     *
     * @param float $nilai Nilai siswa
     * @param string $key Kode mata pelajaran (pai, bahasa_indonesia, dll)
     * @return string Deskripsi capaian khusus mata pelajaran
     */
    protected function generateSpecificCapaian($nilai, $key)
    {
        $siswa = $this->siswa->nama ?? 'Siswa';
        
        // Deskripsi khusus berdasarkan mata pelajaran
        $specificDescriptions = [
            'pai' => [
                90 => "{$siswa} menunjukkan pemahaman yang sangat baik tentang nilai-nilai agama Islam dan dapat menerapkannya dalam kehidupan sehari-hari.",
                80 => "{$siswa} memahami nilai-nilai agama Islam dengan baik dan berusaha menerapkannya.",
                70 => "{$siswa} cukup memahami nilai-nilai dasar agama Islam.",
                60 => "{$siswa} perlu meningkatkan pemahaman tentang nilai-nilai dasar agama Islam.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam memahami nilai-nilai dasar agama Islam."
            ],
            'matematika' => [
                90 => "{$siswa} sangat baik dalam memahami konsep matematika dan dapat menyelesaikan soal-soal dengan sangat baik.",
                80 => "{$siswa} memahami konsep matematika dengan baik dan dapat menyelesaikan berbagai jenis soal.",
                70 => "{$siswa} cukup memahami konsep dasar matematika dan mampu menyelesaikan soal-soal sederhana.",
                60 => "{$siswa} perlu meningkatkan pemahaman konsep dasar matematika.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam memahami konsep dasar matematika."
            ],
            'bahasa_indonesia' => [
                90 => "{$siswa} sangat baik dalam berkomunikasi dan memahami teks bahasa Indonesia.",
                80 => "{$siswa} memiliki kemampuan yang baik dalam berkomunikasi dan memahami teks bahasa Indonesia.",
                70 => "{$siswa} cukup baik dalam berkomunikasi dan memahami teks bahasa Indonesia sederhana.",
                60 => "{$siswa} perlu meningkatkan kemampuan berkomunikasi dan pemahaman teks bahasa Indonesia.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam berkomunikasi dan memahami teks bahasa Indonesia dasar."
            ],
            'ppkn' => [
                90 => "{$siswa} menunjukkan pemahaman sangat baik tentang nilai-nilai Pancasila dan kewarganegaraan.",
                80 => "{$siswa} memiliki pemahaman yang baik tentang nilai-nilai Pancasila dan kewarganegaraan.",
                70 => "{$siswa} cukup memahami nilai-nilai dasar Pancasila dan kewarganegaraan.",
                60 => "{$siswa} perlu meningkatkan pemahaman tentang nilai-nilai Pancasila dan kewarganegaraan.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam memahami nilai dasar Pancasila dan kewarganegaraan."
            ],
            'pjok' => [
                90 => "{$siswa} sangat aktif dalam kegiatan olahraga dan menunjukkan keterampilan motorik yang sangat baik.",
                80 => "{$siswa} aktif dalam kegiatan olahraga dan memiliki keterampilan motorik yang baik.",
                70 => "{$siswa} cukup aktif dalam kegiatan olahraga dan menunjukkan perkembangan keterampilan motorik.",
                60 => "{$siswa} perlu lebih aktif dalam kegiatan olahraga dan meningkatkan keterampilan motorik.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam aktivitas olahraga dan pengembangan keterampilan motorik."
            ],
            'seni_musik' => [
                90 => "{$siswa} menunjukkan apresiasi dan keterampilan musik yang sangat baik.",
                80 => "{$siswa} memiliki apresiasi dan keterampilan musik yang baik.",
                70 => "{$siswa} cukup mampu mengapresiasi dan menunjukkan keterampilan musik dasar.",
                60 => "{$siswa} perlu meningkatkan apresiasi dan keterampilan musik.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam mengembangkan apresiasi dan keterampilan musik."
            ],
            'bahasa_inggris' => [
                90 => "{$siswa} sangat baik dalam berkomunikasi dan memahami teks bahasa Inggris sederhana.",
                80 => "{$siswa} memiliki kemampuan yang baik dalam bahasa Inggris dasar.",
                70 => "{$siswa} cukup memahami kosakata dan kalimat bahasa Inggris sederhana.",
                60 => "{$siswa} perlu meningkatkan pemahaman kosakata dan struktur bahasa Inggris dasar.",
                0 => "{$siswa} membutuhkan bimbingan khusus dalam mempelajari bahasa Inggris dasar."
            ],
        ];
        
        // Jika ada deskripsi khusus untuk mata pelajaran
        if (isset($specificDescriptions[$key])) {
            // Temukan deskripsi berdasarkan rentang nilai
            foreach ($specificDescriptions[$key] as $minNilai => $deskripsi) {
                if ($nilai >= $minNilai) {
                    return $deskripsi;
                }
            }
        }
        
        // Jika tidak ada deskripsi khusus, gunakan deskripsi umum
        $mapelNames = [
            'pai' => 'Pendidikan Agama dan Budi Pekerti',
            'ppkn' => 'Pendidikan Pancasila',
            'bahasa_indonesia' => 'Bahasa Indonesia',
            'matematika' => 'Matematika',
            'pjok' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
            'seni_musik' => 'Seni Musik',
            'bahasa_inggris' => 'Bahasa Inggris'
        ];
        
        $namaMapel = $mapelNames[$key] ?? $key;
        return $this->generateCapaianDeskripsi($nilai, $namaMapel);
    }

    /**
     * Generate rapor dari template
     * 
     * @param bool $bypassValidation Lewati validasi data jika perlu
     * @return array
     */
    public function generate($bypassValidation = false)
    {
        try {
            // 1. Validasi data
            if (!$bypassValidation) {
                $this->validateData();
            }

            // 2. Kumpulkan dan isi data
            $data = $this->collectAllData();
            
            // 3. Dapatkan semua variabel di template
            $variables = $this->processor->getVariables();
            
            Log::info('Variables in template:', [
                'found_variables' => $variables,
                'template_type' => $this->type
            ]);
            
            // 4. Isi semua placeholder
            try {
                foreach ($data as $key => $value) {
                    if (in_array($key, $variables)) {
                        $this->processor->setValue($key, $value ?: '-');
                    }
                }
                
                // Isi placeholder yang ada di template tapi tidak ada di data
                $missingPlaceholders = array_diff($variables, array_keys($data));
                foreach ($missingPlaceholders as $placeholder) {
                    try {
                        $this->processor->setValue($placeholder, '-');
                    } catch (\Exception $e) {
                        Log::warning("Could not set value for missing placeholder '{$placeholder}':", [
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Gagal mengisi placeholder:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'siswa_id' => $this->siswa->id
                ]);
                
                throw new Exception(
                    "Gagal mengisi data ke template. Kemungkinan format template tidak valid. Error: " . $e->getMessage(),
                    self::ERROR_PLACEHOLDER_MISSING
                );
            }
            
            // 5. Generate file
            $filename = $this->generateFilename();
            $outputPath = $this->saveFile($filename);

            return [
                'success' => true,
                'filename' => $filename,
                'path' => "generated/{$filename}"
            ];

        } catch (Exception $e) {
            Log::error('Gagal generate rapor:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'siswa_id' => $this->siswa->id,
                'template_id' => $this->template->id
            ]);
            
            throw new Exception('Gagal generate rapor: ' . $e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Validasi data sebelum generate rapor
     * 
     * @throws Exception
     * @return void
     */
    protected function validateData()
    {
        $semester = $this->type === 'UTS' ? 1 : 2;

        // Validasi template aktif
        if (!$this->template->is_active) {
            throw new Exception(
                'Template rapor belum diaktifkan. Hubungi admin untuk mengaktifkan template.',
                self::ERROR_TEMPLATE_INVALID
            );
        }

        // Validasi apakah siswa memiliki nilai
        $hasAnyNilai = $this->siswa->nilais()
            ->whereHas('mataPelajaran', function($q) use ($semester) {
                $q->where('semester', $semester);
            })
            ->exists();
            
        if (!$hasAnyNilai) {
            throw new Exception(
                'Siswa belum memiliki nilai untuk semester ini. Mohon input nilai terlebih dahulu.',
                self::ERROR_DATA_INCOMPLETE
            );
        }

        // Validasi nilai untuk mata pelajaran tertentu
        // Ini sudah tidak diperlukan karena kita akan menampilkan yang ada saja
        // Tapi bisa dikembalikan jika masih ingin memeriksa mata pelajaran wajib
        /*
        $mapelWajib = ['Matematika', 'Bahasa Indonesia', 'Pendidikan Agama Islam'];
        $missingMapel = [];
        
        foreach ($mapelWajib as $mapel) {
            $hasMapel = $this->siswa->nilais()
                ->whereHas('mataPelajaran', function($q) use ($semester, $mapel) {
                    $q->where('semester', $semester)
                      ->where('nama_pelajaran', 'like', "%{$mapel}%");
                })
                ->exists();
                
            if (!$hasMapel) {
                $missingMapel[] = $mapel;
            }
        }
        
        if (!empty($missingMapel)) {
            throw new Exception(
                'Data nilai mata pelajaran berikut belum ada: ' . implode(', ', $missingMapel),
                self::ERROR_DATA_INCOMPLETE
            );
        }
        */

        // Cek kehadiran
        if (!$this->siswa->absensi()->where('semester', $semester)->exists()) {
            throw new Exception(
                'Data kehadiran siswa belum diisi untuk semester ' . ($semester == 1 ? 'Ganjil' : 'Genap'),
                self::ERROR_DATA_INCOMPLETE
            );
        }
    }
    
    /**
     * Generate nama file rapor
     * 
     * @return string
     */
    protected function generateFilename()
    {
        return sprintf(
            "rapor_%s_%s_%s_%s.docx",
            $this->type,
            $this->siswa->nis ?: 'nonis',
            str_replace(' ', '_', $this->siswa->kelas->nama_kelas),
            time()
        );
    }

    /**
     * Simpan file rapor ke storage
     * 
     * @param string $filename
     * @return string
     * @throws Exception
     */
    protected function saveFile($filename)
    {
        $outputPath = storage_path("app/public/generated/{$filename}");
        
        if (!file_exists(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }
        
        try {
            $this->processor->saveAs($outputPath);
            
            if (!file_exists($outputPath)) {
                throw new Exception("File tidak berhasil disimpan");
            }
            
            Log::info('Rapor berhasil disimpan:', [
                'path' => $outputPath,
                'size' => filesize($outputPath)
            ]);
            
            return "generated/{$filename}";
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan file rapor:', [
                'error' => $e->getMessage(),
                'path' => $outputPath
            ]);
            
            throw new Exception(
                "Gagal menyimpan file rapor: " . $e->getMessage(),
                self::ERROR_FILE_PROCESSING
            );
        }
    }
}