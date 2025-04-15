<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Nilai;
use App\Models\Absensi;
use App\Models\MataPelajaran;
use App\Models\NilaiEkstrakurikuler;
use Illuminate\Support\Facades\DB;

class RaporDataOptimizer
{
    /**
     * Load all data needed for report generation in a single optimized query
     *
     * @param Siswa $siswa
     * @param string $type
     * @param int|null $tahunAjaranId
     * @return array
     */
    public function loadOptimizedData(Siswa $siswa, string $type, ?int $tahunAjaranId = null)
    {
        // Determine semester based on report type
        $semester = $type === 'UTS' ? 1 : 2;
        
        // Start profiling
        $startTime = microtime(true);
        
        // Collect all data in one go
        $result = [
            'siswa' => $this->loadSiswaData($siswa),
            'nilai' => $this->loadNilaiData($siswa, $semester, $tahunAjaranId),
            'ekstrakurikuler' => $this->loadEkstrakurikulerData($siswa, $tahunAjaranId),
            'absensi' => $this->loadAbsensiData($siswa, $semester, $tahunAjaranId),
            'mata_pelajaran' => $this->loadMataPelajaranData($siswa, $semester, $tahunAjaranId),
            'sekolah' => $this->loadSekolahData(),
        ];
        
        // End profiling
        $endTime = microtime(true);
        $loadTime = round(($endTime - $startTime) * 1000, 2); // in milliseconds
        
        $result['meta'] = [
            'load_time_ms' => $loadTime,
            'query_count' => count(DB::getQueryLog()),
        ];
        
        return $result;
    }
    
    /**
     * Load student data with related models
     *
     * @param Siswa $siswa
     * @return array
     */
    protected function loadSiswaData(Siswa $siswa)
    {
        // Eager load all needed relations to reduce N+1 problem
        $siswa->load([
            'kelas.tahunAjaran',
            'kelas.waliKelas'
        ]);
        
        return [
            'id' => $siswa->id,
            'nama' => $siswa->nama,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'jenis_kelamin' => $siswa->jenis_kelamin,
            'agama' => $siswa->agama,
            'tempat_lahir' => $siswa->tempat_lahir,
            'alamat' => $siswa->alamat,
            'nama_ayah' => $siswa->nama_ayah,
            'nama_ibu' => $siswa->nama_ibu,
            'pekerjaan_ayah' => $siswa->pekerjaan_ayah,
            'pekerjaan_ibu' => $siswa->pekerjaan_ibu,
            'alamat_orangtua' => $siswa->alamat_orangtua,
            'wali_siswa' => $siswa->wali_siswa,
            'pekerjaan_wali' => $siswa->pekerjaan_wali,
            'alamat_wali' => $siswa->alamat_wali,
            'kelas' => [
                'id' => $siswa->kelas->id,
                'nomor_kelas' => $siswa->kelas->nomor_kelas,
                'nama_kelas' => $siswa->kelas->nama_kelas,
                'full_kelas' => $siswa->kelas->full_kelas,
                'wali_kelas' => $siswa->kelas->waliKelasName,
                'tahun_ajaran' => $siswa->kelas->tahunAjaran ? [
                    'id' => $siswa->kelas->tahunAjaran->id,
                    'tahun_ajaran' => $siswa->kelas->tahunAjaran->tahun_ajaran,
                    'semester' => $siswa->kelas->tahunAjaran->semester,
                ] : null
            ]
        ];
    }
    
    /**
     * Load nilai data optimized
     *
     * @param Siswa $siswa
     * @param int $semester
     * @param int|null $tahunAjaranId
     * @return array
     */
    protected function loadNilaiData(Siswa $siswa, int $semester, ?int $tahunAjaranId = null)
    {
        // Use DB query builder for optimal performance
        $nilai = DB::table('nilais as n')
            ->select([
                'n.id',
                'n.siswa_id', 
                'n.mata_pelajaran_id',
                'n.nilai_akhir_rapor',
                'n.nilai_tp',
                'n.nilai_lm',
                'n.nilai_tes',
                'n.nilai_non_tes',
                'mp.nama_pelajaran',
                'mp.is_muatan_lokal'
            ])
            ->join('mata_pelajarans as mp', 'n.mata_pelajaran_id', '=', 'mp.id')
            ->where('n.siswa_id', $siswa->id)
            ->where('mp.semester', $semester)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('n.tahun_ajaran_id', $tahunAjaranId);
            })
            ->whereNotNull('n.nilai_akhir_rapor')
            ->get();
            
        // Group by regular subjects and muatan lokal
        $nilaiByCategory = [
            'reguler' => [],
            'muatan_lokal' => []
        ];
        
        foreach ($nilai as $n) {
            $category = $n->is_muatan_lokal ? 'muatan_lokal' : 'reguler';
            $nilaiByCategory[$category][] = (array) $n;
        }
        
        return $nilaiByCategory;
    }
    
    /**
     * Load ekstrakurikuler data
     *
     * @param Siswa $siswa
     * @param int|null $tahunAjaranId
     * @return array
     */
    protected function loadEkstrakurikulerData(Siswa $siswa, ?int $tahunAjaranId = null)
    {
        return DB::table('nilai_ekstrakurikulers as ne')
            ->select([
                'ne.id',
                'ne.nilai',
                'ne.deskripsi',
                'e.nama_ekstrakurikuler',
                'e.pembina'
            ])
            ->join('ekstrakurikulers as e', 'ne.ekstrakurikuler_id', '=', 'e.id')
            ->where('ne.siswa_id', $siswa->id)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('ne.tahun_ajaran_id', $tahunAjaranId);
            })
            ->get()
            ->map(function($item) {
                return (array) $item;
            })
            ->toArray();
    }
    
    /**
     * Load absensi data
     *
     * @param Siswa $siswa
     * @param int $semester
     * @param int|null $tahunAjaranId
     * @return array
     */
    protected function loadAbsensiData(Siswa $siswa, int $semester, ?int $tahunAjaranId = null)
    {
        $absensi = DB::table('absensis')
            ->where('siswa_id', $siswa->id)
            ->where('semester', $semester)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->first();
            
        return $absensi ? (array) $absensi : [
            'sakit' => 0,
            'izin' => 0,
            'tanpa_keterangan' => 0
        ];
    }
    
    /**
     * Load mata pelajaran data
     *
     * @param Siswa $siswa
     * @param int $semester
     * @param int|null $tahunAjaranId
     * @return array
     */
    protected function loadMataPelajaranData(Siswa $siswa, int $semester, ?int $tahunAjaranId = null)
    {
        return DB::table('mata_pelajarans')
            ->select([
                'id',
                'nama_pelajaran',
                'is_muatan_lokal',
                'kelas_id',
                'guru_id'
            ])
            ->where('kelas_id', $siswa->kelas_id)
            ->where('semester', $semester)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->get()
            ->map(function($item) {
                return (array) $item;
            })
            ->toArray();
    }
    
    /**
     * Load school data (from cache preferably)
     *
     * @return array
     */
    protected function loadSekolahData()
    {
        // Try to get from cache first
        $cacheKey = 'profil_sekolah';
        
        if (cache()->has($cacheKey)) {
            return cache()->get($cacheKey);
        }
        
        // If not cached, load from database
        $sekolah = DB::table('profil_sekolah')->first();
        
        $data = $sekolah ? (array) $sekolah : [];
        
        // Cache for 24 hours since school data rarely changes
        cache()->put($cacheKey, $data, now()->addHours(24));
        
        return $data;
    }
    
    /**
     * Process loaded data for template placeholders
     *
     * @param array $data
     * @param string $type
     * @return array
     */
    public function prepareTemplateData(array $data, string $type)
    {
        $placeholders = [];
        
        // Basic student data
        $placeholders['nama_siswa'] = $data['siswa']['nama'] ?? '-';
        $placeholders['nisn'] = $data['siswa']['nisn'] ?? '-';
        $placeholders['nis'] = $data['siswa']['nis'] ?? '-';
        $placeholders['kelas'] = isset($data['siswa']['kelas']) 
            ? $data['siswa']['kelas']['nomor_kelas'] . ' ' . $data['siswa']['kelas']['nama_kelas']
            : '-';
        $placeholders['tahun_ajaran'] = $data['siswa']['kelas']['tahun_ajaran']['tahun_ajaran'] ?? '-';
        
        // Additional student data
        $placeholders['jenis_kelamin'] = $data['siswa']['jenis_kelamin'] ?? '-';
        $placeholders['agama'] = $data['siswa']['agama'] ?? '-';
        $placeholders['tempat_lahir'] = $data['siswa']['tempat_lahir'] ?? '-';
        $placeholders['alamat_siswa'] = $data['siswa']['alamat'] ?? '-';
        $placeholders['nama_ayah'] = $data['siswa']['nama_ayah'] ?? '-';
        $placeholders['nama_ibu'] = $data['siswa']['nama_ibu'] ?? '-';
        
        // Process nilai (regular subjects)
        $this->processNilaiData($data['nilai']['reguler'] ?? [], $placeholders);
        
        // Process nilai muatan lokal
        $this->processMuatanLokalData($data['nilai']['muatan_lokal'] ?? [], $placeholders);
        
        // Process ekstrakurikuler
        $this->processEkstrakurikulerData($data['ekstrakurikuler'] ?? [], $placeholders);
        
        // Process absensi
        $placeholders['sakit'] = $data['absensi']['sakit'] ?? '0';
        $placeholders['izin'] = $data['absensi']['izin'] ?? '0';
        $placeholders['tanpa_keterangan'] = $data['absensi']['tanpa_keterangan'] ?? '0';
        
        // School data
        $placeholders['nama_sekolah'] = $data['sekolah']['nama_sekolah'] ?? '-';
        $placeholders['alamat_sekolah'] = $data['sekolah']['alamat'] ?? '-';
        $placeholders['kelurahan'] = $data['sekolah']['kelurahan'] ?? '-';
        $placeholders['kecamatan'] = $data['sekolah']['kecamatan'] ?? '-';
        $placeholders['kabupaten'] = $data['sekolah']['kabupaten'] ?? '-';
        $placeholders['provinsi'] = $data['sekolah']['provinsi'] ?? '-';
        $placeholders['kode_pos'] = $data['sekolah']['kode_pos'] ?? '-';
        $placeholders['nomor_telepon'] = $data['sekolah']['telepon'] ?? '-';
        $placeholders['email_sekolah'] = $data['sekolah']['email_sekolah'] ?? '-';
        $placeholders['website'] = $data['sekolah']['website'] ?? '-';
        $placeholders['npsn'] = $data['sekolah']['npsn'] ?? '-';
        
        // Other fields
        $placeholders['kepala_sekolah'] = $data['sekolah']['kepala_sekolah'] ?? '-';
        $placeholders['nip_kepala_sekolah'] = $data['sekolah']['nip_kepala_sekolah'] ?? '-';
        $placeholders['wali_kelas'] = $data['siswa']['kelas']['wali_kelas'] ?? '-';
        $placeholders['nip_wali_kelas'] = $data['sekolah']['nip_wali_kelas'] ?? '-';
        $placeholders['tanggal_terbit'] = date('d-m-Y');
        $placeholders['fase'] = $this->determineFase($data['siswa']['kelas']['nomor_kelas'] ?? 0);
        $placeholders['semester'] = isset($data['siswa']['kelas']['tahun_ajaran']) 
            ? ($data['siswa']['kelas']['tahun_ajaran']['semester'] == 1 ? 'Ganjil' : 'Genap')
            : '-';
            
        // Catatan guru (placeholder)
        $placeholders['catatan_guru'] = '-';
        
        return $placeholders;
    }
    
    /**
     * Process regular nilai data
     *
     * @param array $nilaiData
     * @param array &$placeholders
     * @return void
     */
    protected function processNilaiData(array $nilaiData, array &$placeholders)
    {
        // Default nilai keys with their placeholder names
        $mapelMapping = [
            'Pendidikan Agama Islam' => 'pai',
            'PAI' => 'pai',
            'Agama Islam' => 'pai',
            'Pendidikan Agama dan Budi Pekerti' => 'pai',
            'PPKN' => 'ppkn',
            'PKN' => 'ppkn',
            'Pendidikan Pancasila' => 'ppkn',
            'Pendidikan Kewarganegaraan' => 'ppkn',
            'Pendidikan Pancasila dan Kewarganegaraan' => 'ppkn',
            'Bahasa Indonesia' => 'bahasa_indonesia',
            'B. Indonesia' => 'bahasa_indonesia',
            'BI' => 'bahasa_indonesia',
            'Matematika' => 'matematika',
            'MTK' => 'matematika',
            'Math' => 'matematika',
            'PJOK' => 'pjok',
            'Pendidikan Jasmani' => 'pjok',
            'Olahraga' => 'pjok',
            'Pendidikan Jasmani Olahraga dan Kesehatan' => 'pjok',
            'Seni Musik' => 'seni_musik',
            'Musik' => 'seni_musik',
            'Kesenian' => 'seni_musik',
            'Seni' => 'seni_musik',
            'Seni Budaya' => 'seni_musik',
            'SBK' => 'seni_musik',
            'Bahasa Inggris' => 'bahasa_inggris',
            'B. Inggris' => 'bahasa_inggris',
            'English' => 'bahasa_inggris',
            'IPS' => 'ips',
            'Ilmu Pengetahuan Sosial' => 'ips',
            'Ilmu Sosial' => 'ips'
        ];
        
        // Dynamic placeholders with numbers
        $dynamicCount = 1;
        
        // Process each nilai
        foreach ($nilaiData as $nilai) {
            $mapelName = $nilai['nama_pelajaran'];
            $nilaiValue = $nilai['nilai_akhir_rapor'];
            
            // Try to match with standard mappings first
            $placeholderKey = null;
            foreach ($mapelMapping as $mapelPattern => $key) {
                if (stripos($mapelName, $mapelPattern) !== false || strtolower($mapelName) === strtolower($mapelPattern)) {
                    $placeholderKey = $key;
                    break;
                }
            }
            
            // If found, set the direct placeholder
            if ($placeholderKey) {
                $placeholders["nilai_{$placeholderKey}"] = number_format($nilaiValue, 1);
                $placeholders["capaian_{$placeholderKey}"] = $this->generateCapaianDeskripsi($nilaiValue, $mapelName);
            }
            
            // Always add to dynamic numbered placeholders
            if ($dynamicCount <= 10) {
                $placeholders["nama_matapelajaran{$dynamicCount}"] = $mapelName;
                $placeholders["nilai_matapelajaran{$dynamicCount}"] = number_format($nilaiValue, 1);
                $placeholders["capaian_matapelajaran{$dynamicCount}"] = $this->generateCapaianDeskripsi($nilaiValue, $mapelName);
                $dynamicCount++;
            }
        }
        
        // Fill in any remaining dynamic placeholders with defaults
        for ($i = $dynamicCount; $i <= 10; $i++) {
            $placeholders["nama_matapelajaran{$i}"] = '-';
            $placeholders["nilai_matapelajaran{$i}"] = '-';
            $placeholders["capaian_matapelajaran{$i}"] = '-';
        }
    }
    
    /**
     * Process muatan lokal data
     *
     * @param array $mulokData
     * @param array &$placeholders
     * @return void
     */
    protected function processMuatanLokalData(array $mulokData, array &$placeholders)
    {
        // Process muatan lokal (numbered 1-5)
        for ($i = 1; $i <= 5; $i++) {
            if (isset($mulokData[$i-1])) {
                $mulok = $mulokData[$i-1];
                $placeholders["nama_mulok{$i}"] = $mulok['nama_pelajaran'];
                $placeholders["nilai_mulok{$i}"] = number_format($mulok['nilai_akhir_rapor'], 1);
                $placeholders["capaian_mulok{$i}"] = $this->generateCapaianDeskripsi($mulok['nilai_akhir_rapor'], $mulok['nama_pelajaran']);
            } else {
                $placeholders["nama_mulok{$i}"] = '-';
                $placeholders["nilai_mulok{$i}"] = '-';
                $placeholders["capaian_mulok{$i}"] = '-';
            }
        }
    }
    
    /**
     * Process ekstrakurikuler data
     *
     * @param array $ekskulData
     * @param array &$placeholders
     * @return void
     */
    protected function processEkstrakurikulerData(array $ekskulData, array &$placeholders)
    {
        // Process ekstrakurikuler (numbered 1-6)
        for ($i = 1; $i <= 6; $i++) {
            if (isset($ekskulData[$i-1])) {
                $ekskul = $ekskulData[$i-1];
                $placeholders["ekskul{$i}_nama"] = $ekskul['nama_ekstrakurikuler'];
                $placeholders["ekskul{$i}_keterangan"] = $ekskul['deskripsi'] ?: '-';
            } else {
                $placeholders["ekskul{$i}_nama"] = '-';
                $placeholders["ekskul{$i}_keterangan"] = '-';
            }
        }
    }
    
    /**
     * Generate capaian deskripsi based on nilai
     *
     * @param float $nilai
     * @param string $namaMapel
     * @return string
     */
    protected function generateCapaianDeskripsi($nilai, $namaMapel)
    {
        if ($nilai >= 90) {
            return "Siswa menunjukkan penguasaan yang sangat baik dalam mata pelajaran {$namaMapel}. Mampu memahami konsep, menerapkan, dan menganalisis dengan sangat baik.";
        } elseif ($nilai >= 80) {
            return "Siswa menunjukkan penguasaan yang baik dalam mata pelajaran {$namaMapel}. Mampu memahami konsep dan menerapkannya dengan baik.";
        } elseif ($nilai >= 70) {
            return "Siswa menunjukkan penguasaan yang cukup dalam mata pelajaran {$namaMapel}. Sudah mampu memahami konsep dasar dengan baik.";
        } elseif ($nilai >= 60) {
            return "Siswa menunjukkan penguasaan yang sedang dalam mata pelajaran {$namaMapel}. Perlu meningkatkan pemahaman konsep dasar.";
        } else {
            return "Siswa perlu bimbingan lebih lanjut dalam mata pelajaran {$namaMapel}. Disarankan untuk mengulang pembelajaran materi dasar.";
        }
    }
    
    /**
     * Determine fase based on kelas
     *
     * @param int $kelas
     * @return string
     */
    protected function determineFase($kelas)
    {
        if ($kelas <= 2) {
            return 'A';
        } elseif ($kelas <= 4) {
            return 'B';
        } else {
            return 'C';
        }
    }
}