<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\Siswa;
use App\Models\ReportPlaceholder;
use App\Models\ProfilSekolah;
use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Exceptions\RaporException;
use Exception;
use App\Helpers\FileNameHelper;

class RaporTemplateProcessor 
{
    // Constants for error types
    const ERROR_TEMPLATE_MISSING = 1001;
    const ERROR_TEMPLATE_INVALID = 1002;
    const ERROR_DATA_INCOMPLETE = 1003;
    const ERROR_PLACEHOLDER_MISSING = 1004;
    const ERROR_FILE_PROCESSING = 1005;

    protected $processor;
    protected $template;
    protected $siswa;
    protected $type;
    protected $placeholders;
    protected $schoolProfile;
    protected $tahunAjaranId; // Tambahkan property untuk menyimpan tahun ajaran ID

    public function __construct(ReportTemplate $template, Siswa $siswa, $type = 'UTS', $tahunAjaranId = null)
    {
        $this->template = $template;
        $this->siswa = $siswa;
        $this->type = $type;
        $this->schoolProfile = ProfilSekolah::first();
        // Ambil tahun ajaran dari parameter, session, atau dari kelas siswa
        $this->tahunAjaranId = $tahunAjaranId ?: session('tahun_ajaran_id') ?: ($siswa->kelas->tahun_ajaran_id ?? null);
        
        // Log untuk debugging
        Log::info('RaporTemplateProcessor initialized:', [
            'siswa_id' => $siswa->id, 
            'siswa_name' => $siswa->nama,
            'kelas' => $siswa->kelas->nama_kelas ?? 'Unknown',
            'template_id' => $template->id,
            'type' => $type,
            'tahun_ajaran_id' => $this->tahunAjaranId
        ]);

        // Validasi template path tidak kosong
        if (empty($template->path)) {
            throw new RaporException(
                "Path template kosong. Hubungi admin untuk upload template baru.",
                'template_missing',
                self::ERROR_TEMPLATE_MISSING
            );
        }
    
        // Log untuk debugging
        Log::info('Template Info:', [
            'template_id' => $template->id,
            'filename' => $template->filename,
            'path' => $template->path,
            'is_active' => $template->is_active,
            'tahun_ajaran_id' => $this->tahunAjaranId // Log tahun ajaran yang digunakan
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
            throw new RaporException(
                "Template file tidak ditemukan: {$templatePath}. Hubungi admin untuk upload template baru.",
                'template_missing',
                self::ERROR_TEMPLATE_MISSING
            );
        }
    
        if (!is_file($templatePath)) {
            throw new RaporException(
                "Path bukan merupakan file yang valid: {$templatePath}. Hubungi admin untuk upload template baru.",
                'template_invalid',
                self::ERROR_TEMPLATE_INVALID
            );
        }
    
        try {
            $this->processor = new TemplateProcessor($templatePath);
        } catch (\Exception $e) {
            throw new RaporException(
                "Gagal memproses template: " . $e->getMessage() . ". Hubungi admin untuk perbaiki template.",
                'template_invalid',
                self::ERROR_TEMPLATE_INVALID
            );
        }
    
        $this->placeholders = ReportPlaceholder::all()->groupBy('category');
    }

    protected function getTemplateForSiswa(Siswa $siswa, $type, $tahunAjaranId = null)
    {
        $tahunAjaranId = $tahunAjaranId ?: session('tahun_ajaran_id');
        
        // First look for class-specific template
        $template = ReportTemplate::where('type', $type)
            ->where('kelas_id', $siswa->kelas_id)
            ->where('is_active', true)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->first();
        
        // If not found, look for global template
        if (!$template) {
            $template = ReportTemplate::where('type', $type)
                ->whereNull('kelas_id')
                ->where('is_active', true)
                ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                    return $query->where('tahun_ajaran_id', $tahunAjaranId);
                })
                ->first();
        }
        
        // Log untuk debugging
        Log::info('Template selection for siswa:', [
            'siswa_id' => $siswa->id,
            'kelas_id' => $siswa->kelas_id,
            'type' => $type,
            'tahun_ajaran_id' => $tahunAjaranId,
            'template_found' => $template ? 'Yes' : 'No',
            'template_id' => $template ? $template->id : null
        ]);
        
        return $template;
    }
    
    
    /**
     * Mengumpulkan semua data yang diperlukan untuk template rapor
     * 
     * @return array
     */
    protected function collectAllData()
    {
        $semester = $this->type === 'UTS' ? 1 : 2;
        $tahunAjaranId = $this->tahunAjaranId;
        
        // Data Siswa
        $data = [
            'nama_siswa' => $this->siswa->nama,
            'nisn' => $this->siswa->nisn ?: '-',
            'nis' => $this->siswa->nis ?: '-',
            'kelas' => $this->siswa->kelas->nomor_kelas . ' ' . $this->siswa->kelas->nama_kelas,
            'tahun_ajaran' => $this->siswa->kelas->tahunAjaran ? $this->siswa->kelas->tahunAjaran->tahun_ajaran : ($this->schoolProfile->tahun_pelajaran ?? '-'),
            'tempat_lahir' => $this->siswa->tempat_lahir ?? '-',
            'jenis_kelamin' => $this->siswa->jenis_kelamin ?? '-',
            'agama' => $this->siswa->agama ?? '-',
            'alamat_siswa' => $this->siswa->alamat ?? '-',
            'nama_ayah' => $this->siswa->nama_ayah ?? '-',
            'nama_ibu' => $this->siswa->nama_ibu ?? '-',
            'pekerjaan_ayah' => $this->siswa->pekerjaan_ayah ?? '-',
            'pekerjaan_ibu' => $this->siswa->pekerjaan_ibu ?? '-',
            'alamat_orangtua' => $this->siswa->alamat_orangtua ?? '-',
            'wali_siswa' => $this->siswa->wali_siswa ?? '-',
            'pekerjaan_wali' => $this->siswa->pekerjaan_wali ?? '-',
            'alamat_wali' => $this->siswa->alamat_wali ?? '-',
            'fase' => $this->determineFase($this->siswa->kelas->nomor_kelas),
            'semester' => $this->schoolProfile->semester == 1 ? 'Ganjil' : 'Genap',
        ];
    
        // Data Nilai - Filter berdasarkan tahun ajaran yang dipilih
        $nilaiQuery = $this->siswa->nilais()
            ->with(['mataPelajaran'])
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            });
        
        $nilaiCollection = $nilaiQuery->get();
        
        // Grouping by mata pelajaran
        $nilai = $nilaiCollection->groupBy('mataPelajaran.nama_pelajaran');
        
        Log::info('Data nilai yang diambil:', [
            'siswa_id' => $this->siswa->id,
            'mapel_count' => $nilai->count(),
            'mapel_list' => $nilai->keys()->toArray(),
            'tahun_ajaran_id' => $tahunAjaranId
        ]);
    
        // Pisahkan mata pelajaran reguler dan muatan lokal
        $mapelReguler = $nilaiCollection->groupBy('mataPelajaran.nama_pelajaran')
            ->filter(function($value, $key) {
                return $value->first() && 
                       $value->first()->mataPelajaran && 
                       $value->first()->mataPelajaran->is_muatan_lokal == 0;
            });
            
        $mulok = $nilaiCollection->groupBy('mataPelajaran.nama_pelajaran')
            ->filter(function($value, $key) {
                return $value->first() && 
                       $value->first()->mataPelajaran && 
                       $value->first()->mataPelajaran->is_muatan_lokal == 1;
            });
            
        Log::info('Pembagian mata pelajaran:', [
            'reguler_count' => $mapelReguler->count(),
            'mulok_count' => $mulok->count(),
            'reguler_list' => $mapelReguler->keys()->toArray(),
            'mulok_list' => $mulok->keys()->toArray()
        ]);
    
        // Definisi mata pelajaran wajib dengan urutan tertentu dan sinonim yang lebih tepat
        $priorityMapel = [
            'pai' => ['Pendidikan Agama Islam', 'PAI', 'Agama Islam', 'Pendidikan Agama dan Budi Pekerti'],
            'ppkn' => ['PPKN', 'PKN', 'Pendidikan Pancasila', 'Pendidikan Kewarganegaraan', 'Pendidikan Pancasila dan Kewarganegaraan'],
            'bahasa_indonesia' => ['Bahasa Indonesia', 'B. Indonesia', 'BI'],
            'matematika' => ['Matematika', 'MTK', 'Math'],
            'pjok' => ['PJOK', 'Pendidikan Jasmani', 'Olahraga', 'Pendidikan Jasmani Olahraga dan Kesehatan'],
            'seni_musik' => ['Seni Musik', 'Musik', 'Kesenian', 'Seni', 'Seni Budaya', 'SBK'],
            'bahasa_inggris' => ['Bahasa Inggris', 'B. Inggris', 'English'],
            'ips' => ['IPS', 'Ilmu Pengetahuan Sosial', 'Ilmu Sosial'] // Tambahkan IPS
        ];
    
        // Urutan standar mata pelajaran
        $mapelOrder = ['pai', 'ppkn', 'bahasa_indonesia', 'matematika', 'ips', 'seni_musik', 'pjok', 'bahasa_inggris'];
    
        // Nama-nama default untuk mapel (untuk placeholder)
        $mapelNames = [
            'pai' => 'Pendidikan Agama dan Budi Pekerti',
            'ppkn' => 'Pendidikan Pancasila',
            'bahasa_indonesia' => 'Bahasa Indonesia',
            'matematika' => 'Matematika',
            'ips' => 'Ilmu Pengetahuan Sosial',
            'pjok' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan',
            'seni_musik' => 'Seni Musik',
            'bahasa_inggris' => 'Bahasa Inggris'
        ];
    
        // Identifikasi semua mata pelajaran terlebih dahulu
        $mapelIdentified = [];
        $processedMapelNames = []; // Track nama mata pelajaran yang sudah diproses
    
        foreach ($mapelReguler as $mapelName => $nilaiMapel) {
            $matchedKey = $this->findMatchingMapel($mapelName, $priorityMapel);
            
            if ($matchedKey) {
                // Mencegah duplikasi: jangan tambahkan mata pelajaran dengan key yang sama
                if (!isset($mapelIdentified[$matchedKey])) {
                    $mapelIdentified[$matchedKey] = [
                        'name' => $mapelName,
                        'nilai' => $nilaiMapel
                    ];
                    $processedMapelNames[] = $mapelName;
                    
                    Log::info("Mata pelajaran diidentifikasi", [
                        'nama' => $mapelName,
                        'key' => $matchedKey
                    ]);
                } else {
                    Log::warning("Duplikasi mata pelajaran terdeteksi", [
                        'existing' => $mapelIdentified[$matchedKey]['name'],
                        'duplicate' => $mapelName,
                        'key' => $matchedKey
                    ]);
                }
            }
        }
    
        // Siapkan struktur data untuk placeholder dinamis
        $dynamicPlaceholders = [];
        $mapelCount = 1;
        $processedKeys = []; // Track key yang sudah diproses
        
        // Proses mata pelajaran berdasarkan urutan prioritas
        foreach ($mapelOrder as $key) {
            if (isset($mapelIdentified[$key])) {
                $mapelInfo = $mapelIdentified[$key];
                $mapelName = $mapelInfo['name'];
                $nilaiMapel = $mapelInfo['nilai'];
                
                if (in_array($key, $processedKeys)) {
                    Log::warning("Mata pelajaran dengan key '$key' sudah diproses. Mengabaikan untuk mencegah duplikasi.", [
                        'mapel_name' => $mapelName
                    ]);
                    continue;
                }
                
                // Tandai key ini sudah diproses
                $processedKeys[] = $key;
                
                // Cari nilai akhir rapor yang sesuai dengan tahun ajaran
                $nilaiAkhir = $nilaiMapel
                    ->when($tahunAjaranId, function($collection) use ($tahunAjaranId) {
                        return $collection->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->where('nilai_akhir_rapor', '!=', null)
                    ->first();
                
                if ($nilaiAkhir) {
                    $nilaiValue = $nilaiAkhir->nilai_akhir_rapor;
                    $data["nilai_$key"] = number_format($nilaiValue, 1);
                    $data["capaian_$key"] = $nilaiAkhir->deskripsi ?? 
                        $this->generateSpecificCapaian($nilaiValue, $key);
                        
                    // Tambahkan ke placeholder dinamis
                    $dynamicPlaceholders[$mapelCount] = [
                        'nama' => $mapelName,
                        'nilai' => $data["nilai_$key"],
                        'capaian' => $data["capaian_$key"]
                    ];
                    
                    $mapelCount++;
                    
                    Log::info("Mata pelajaran $key diproses", [
                        'nama' => $mapelName,
                        'nilai' => $nilaiValue,
                        'placeholder_position' => $mapelCount - 1,
                        'tahun_ajaran_id' => $tahunAjaranId
                    ]);
                } else {
                    // Jika tidak ada nilai_akhir_rapor, gunakan rata-rata nilai lain dengan filter tahun ajaran
                    $avgNilai = $nilaiMapel
                        ->when($tahunAjaranId, function($collection) use ($tahunAjaranId) {
                            return $collection->where('tahun_ajaran_id', $tahunAjaranId);
                        })
                        ->avg('nilai_tp');
                        
                    if ($avgNilai) {
                        $data["nilai_$key"] = number_format($avgNilai, 1);
                        $data["capaian_$key"] = $this->generateSpecificCapaian($avgNilai, $key);
                        
                        // Tambahkan ke placeholder dinamis
                        $dynamicPlaceholders[$mapelCount] = [
                            'nama' => $mapelName,
                            'nilai' => $data["nilai_$key"],
                            'capaian' => $data["capaian_$key"]
                        ];
                        
                        $mapelCount++;
                    } else {
                        $data["nilai_$key"] = '-';
                        $data["capaian_$key"] = '-';
                    }
                }
            } else {
                // Jika key tidak ada di data, set nilai default
                $data["nilai_$key"] = '-';
                $data["capaian_$key"] = '-';
                
                Log::info("Mata pelajaran $key tidak ditemukan dalam data siswa");
            }
        }
    
        // Proses mata pelajaran reguler lainnya yang belum diidentifikasi
        foreach ($mapelReguler as $mapelName => $nilaiMapel) {
            if (!in_array($mapelName, $processedMapelNames) && $mapelCount <= 10) {
                // Cari nilai akhir rapor dengan filter tahun ajaran
                $nilaiAkhir = $nilaiMapel
                    ->when($tahunAjaranId, function($collection) use ($tahunAjaranId) {
                        return $collection->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->where('nilai_akhir_rapor', '!=', null)
                    ->first();
                
                if ($nilaiAkhir) {
                    $nilaiValue = $nilaiAkhir->nilai_akhir_rapor;
                    
                    // Tambahkan ke placeholder dinamis
                    $dynamicPlaceholders[$mapelCount] = [
                        'nama' => $mapelName,
                        'nilai' => number_format($nilaiValue, 1),
                        'capaian' => $nilaiAkhir->deskripsi ?? 
                            $this->generateCapaianDeskripsi($nilaiValue, $mapelName)
                    ];
                    
                    $processedMapelNames[] = $mapelName;
                    $mapelCount++;
                    
                    Log::info("Mata pelajaran lainnya diproses", [
                        'nama' => $mapelName,
                        'nilai' => $nilaiValue,
                        'placeholder_position' => $mapelCount - 1,
                        'tahun_ajaran_id' => $tahunAjaranId
                    ]);
                }
            }
        }
    
        // Isi placeholder dinamis di template
        for ($i = 1; $i <= 10; $i++) {
            if (isset($dynamicPlaceholders[$i])) {
                $data["nama_matapelajaran$i"] = $dynamicPlaceholders[$i]['nama'];
                $data["nilai_matapelajaran$i"] = $dynamicPlaceholders[$i]['nilai'];
                $data["capaian_matapelajaran$i"] = $dynamicPlaceholders[$i]['capaian'];
            } else {
                $data["nama_matapelajaran$i"] = '-';
                $data["nilai_matapelajaran$i"] = '-';
                $data["capaian_matapelajaran$i"] = '-';
            }
        }
    
        // Proses Muatan Lokal dengan filter tahun ajaran
        $mulokCount = 1;
        foreach ($mulok as $nama => $nilaiMulok) {
            if ($mulokCount <= 5) {
                // Filter nilai berdasarkan tahun ajaran
                $nilaiAkhir = $nilaiMulok
                    ->when($tahunAjaranId, function($collection) use ($tahunAjaranId) {
                        return $collection->where('tahun_ajaran_id', $tahunAjaranId);
                    })
                    ->where('nilai_akhir_rapor', '!=', null)
                    ->first();
                
                // Nama muatan lokal
                $data["nama_mulok$mulokCount"] = $nama;
                
                if ($nilaiAkhir) {
                    $nilaiValue = $nilaiAkhir->nilai_akhir_rapor;
                    $data["nilai_mulok$mulokCount"] = number_format($nilaiValue, 1);
                    $data["capaian_mulok$mulokCount"] = $nilaiAkhir->deskripsi ?? 
                        $this->generateCapaianDeskripsi($nilaiValue, $nama);
                } else {
                    // Jika tidak ada nilai_akhir_rapor, cari alternatif dengan filter tahun ajaran
                    $avgNilai = $nilaiMulok
                        ->when($tahunAjaranId, function($collection) use ($tahunAjaranId) {
                            return $collection->where('tahun_ajaran_id', $tahunAjaranId);
                        })
                        ->avg('nilai_tp');
                        
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
    
        // Data Ekstrakurikuler dengan filter tahun ajaran
        $ekstrakurikuler = $this->siswa->nilaiEkstrakurikuler()
            ->with('ekstrakurikuler')
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
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
    
        // Data Kehadiran dengan filter tahun ajaran
        $absensi = $this->siswa->absensi()
            ->where('semester', $semester)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->first();
            
        if ($absensi) {
            $data['sakit'] = $absensi->sakit ?: '0';
            $data['izin'] = $absensi->izin ?: '0';
            $data['tanpa_keterangan'] = $absensi->tanpa_keterangan ?: '0';
        } else {
            $data['sakit'] = '0';
            $data['izin'] = '0';
            $data['tanpa_keterangan'] = '0';
        }
        
        // Data sekolah dan lainnya
        if ($this->schoolProfile) {
            $data['nomor_telepon'] = $this->schoolProfile->telepon ?: '-';
            $data['kepala_sekolah'] = $this->schoolProfile->kepala_sekolah ?: '-';
            $data['wali_kelas'] = $this->siswa->kelas->waliKelasName ?: '-';
            $data['nip_kepala_sekolah'] = $this->schoolProfile->nip_kepala_sekolah ?? '-';
            $data['nip_wali_kelas'] = '-'; // Tambahkan jika ada
            $data['tanggal_terbit'] = date('d-m-Y');
            $data['tempat_terbit'] = $this->schoolProfile->tempat_terbit ?: '-';
            
            // Data profil sekolah untuk template UAS
            $data['nama_sekolah'] = $this->schoolProfile->nama_sekolah ?: '-';
            $data['alamat_sekolah'] = $this->schoolProfile->alamat ?: '-';
            $data['kelurahan'] = $this->schoolProfile->kelurahan ?? '-';
            $data['kecamatan'] = $this->schoolProfile->kecamatan ?? '-';
            $data['kabupaten'] = $this->schoolProfile->kabupaten ?? '-';
            $data['provinsi'] = $this->schoolProfile->provinsi ?? '-';
            $data['kode_pos'] = $this->schoolProfile->kode_pos ?: '-';
            $data['website'] = $this->schoolProfile->website ?: '-';
            $data['email_sekolah'] = $this->schoolProfile->email_sekolah ?: '-';
            $data['npsn'] = $this->schoolProfile->npsn ?: '-';
        } else {
            $data['nomor_telepon'] = '-';
            $data['kepala_sekolah'] = '-';
            $data['wali_kelas'] = '-';
            $data['tanggal_terbit'] = date('d-m-Y');
            $data['tempat_terbit'] = '-';
            
            // Default untuk data profil sekolah jika tidak ada
            $data['nama_sekolah'] = '-';
            $data['alamat_sekolah'] = '-';
            $data['kelurahan'] = '-';
            $data['kecamatan'] = '-';
            $data['kabupaten'] = '-';
            $data['provinsi'] = '-';
            $data['kode_pos'] = '-';
            $data['website'] = '-';
            $data['email_sekolah'] = '-';
            $data['npsn'] = '-';
        }
        
        // Catatan guru
        $data['catatan_guru'] = '-';
    
        // Log data akhir yang akan diisi ke template
        Log::info('Data placeholder yang telah disiapkan:', [
            'mata_pelajaran_count' => count(array_filter(array_keys($data), function($key) {
                return strpos($key, 'nama_matapelajaran') === 0;
            })),
            'mulok_count' => count(array_filter(array_keys($data), function($key) {
                return strpos($key, 'nama_mulok') === 0;
            })),
            'tahun_ajaran_id' => $tahunAjaranId
        ]);
    
        return $data;
    }
    
    // Bagian selanjutnya seperti findMatchingMapel(), determineFase(), dll. tetap sama
    // ...
    
    /**
     * Cari mata pelajaran yang cocok berdasarkan nama
     * 
     * @param string $mapelName Nama mata pelajaran
     * @param array $priorities Daftar prioritas mapel
     * @return string|null Kunci mata pelajaran yang cocok atau null jika tidak ditemukan
     */
    protected function findMatchingMapel($mapelName, $priorities)
    {
        // Normalisasi nama mapel (lowercase, hapus spasi berlebih)
        $normalizedName = strtolower(trim($mapelName));
        
        // Log untuk debugging
        Log::info('Mencoba mencocokkan mata pelajaran:', [
            'mapel_name' => $mapelName,
            'normalized' => $normalizedName
        ]);
        
        foreach ($priorities as $key => $keywords) {
            foreach ($keywords as $keyword) {
                $normalizedKeyword = strtolower(trim($keyword));
                
                // Exact match paling diutamakan
                if ($normalizedName === $normalizedKeyword) {
                    Log::info('Exact match ditemukan', [
                        'mapel' => $mapelName,
                        'matched_with' => $keyword,
                        'key' => $key
                    ]);
                    return $key;
                }
                
                // Partial match berikutnya 
                if (strpos($normalizedName, $normalizedKeyword) !== false) {
                    // Pastikan ini bukan partial match yang ambigu
                    // Misalnya, "Pendidikan" bisa merujuk ke banyak mata pelajaran
                    if (strlen($normalizedKeyword) > 5) {
                        Log::info('Partial match ditemukan', [
                            'mapel' => $mapelName,
                            'matched_with' => $keyword,
                            'key' => $key
                        ]);
                        return $key;
                    }
                }
                
                // Cek similaritas teks (terakhir dan dengan threshold yang lebih tinggi)
                $similarity = similar_text($normalizedName, $normalizedKeyword) / max(strlen($normalizedName), strlen($normalizedKeyword));
                if ($similarity > 0.8) { // Threshold 80% (lebih tinggi)
                    Log::info('Similarity match ditemukan', [
                        'mapel' => $mapelName,
                        'matched_with' => $keyword,
                        'similarity' => $similarity,
                        'key' => $key
                    ]);
                    return $key;
                }
            }
        }

        Log::info('Tidak ada kecocokan untuk mata pelajaran', ['mapel' => $mapelName]);
        return null;
    }
    /**
     * Tentukan fase berdasarkan kelas
     * 
     * @param int $kelas Nomor kelas
     * @return string Fase pembelajaran
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
            Log::info('Starting generate() with template type: ' . $this->type, [
                'tahun_ajaran_id' => $this->tahunAjaranId // Log tahun ajaran yang digunakan
            ]);
            
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
                'template_type' => $this->type,
                'tahun_ajaran_id' => $this->tahunAjaranId // Log tahun ajaran yang digunakan
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
                    'siswa_id' => $this->siswa->id,
                    'tahun_ajaran_id' => $this->tahunAjaranId // Log tahun ajaran untuk debug
                ]);
                
                throw new RaporException(
                    "Gagal mengisi data ke template. Kemungkinan format template tidak valid. Error: " . $e->getMessage(),
                    'placeholder_missing',
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

        } catch (RaporException $e) {
            Log::error('Gagal generate rapor (RaporException):', [
                'error' => $e->getMessage(),
                'error_type' => $e->getErrorType(),
                'trace' => $e->getTraceAsString(),
                'siswa_id' => $this->siswa->id,
                'template_id' => $this->template->id,
                'tahun_ajaran_id' => $this->tahunAjaranId // Log tahun ajaran untuk debug
            ]);
            
            throw $e;
        } catch (\Exception $e) {
            Log::error('Gagal generate rapor (Exception):', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'siswa_id' => $this->siswa->id,
                'template_id' => $this->template->id,
                'tahun_ajaran_id' => $this->tahunAjaranId // Log tahun ajaran untuk debug
            ]);
            
            throw new RaporException('Gagal generate rapor: ' . $e->getMessage(), 'general_error', 500, $e);
        }
    }

   /**
     * Validasi data sebelum generate rapor
     * 
     * @throws RaporException
     * @return void
     */
    protected function validateData()
    {
        $tahunAjaranId = $this->tahunAjaranId;

        // Validasi template aktif
        if (!$this->template->is_active) {
            throw new RaporException(
                'Template rapor belum diaktifkan. Hubungi admin untuk mengaktifkan template.',
                'template_invalid',
                self::ERROR_TEMPLATE_INVALID
            );
        }

        // Validasi apakah siswa memiliki nilai untuk tahun ajaran yang aktif
        $hasAnyNilai = $this->siswa->nilais()
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->exists();
            
        if (!$hasAnyNilai) {
            throw new RaporException(
                'Siswa belum memiliki nilai pada tahun ajaran ini. Mohon input nilai terlebih dahulu.',
                'data_incomplete',
                self::ERROR_DATA_INCOMPLETE
            );
        }

        // Cek kehadiran untuk tahun ajaran yang aktif
        $hasAbsensi = $this->siswa->absensi()
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->exists();
            
        if (!$hasAbsensi) {
            throw new RaporException(
                'Data kehadiran siswa pada tahun ajaran ini belum diisi.',
                'data_incomplete',
                self::ERROR_DATA_INCOMPLETE
            );
        }
    }

    /**
     * Generate nama file rapor using the helper
     * 
     * @return string
     */
    protected function generateFilename()
    {
        // Get tahun ajaran info
        $tahunAjaranText = null;
        if ($this->tahunAjaranId) {
            $tahunAjaran = \App\Models\TahunAjaran::find($this->tahunAjaranId);
            if ($tahunAjaran) {
                $tahunAjaranText = $tahunAjaran->tahun_ajaran;
            }
        }
        
        // Call the helper to generate a consistent filename
        return FileNameHelper::generateReportFilename(
            $this->type,
            $this->siswa->nama,
            $this->siswa->kelas->nomor_kelas . $this->siswa->kelas->nama_kelas,
            $tahunAjaranText
        );
    }

     /**
     * Simpan file rapor ke storage
     * 
     * @param string $filename
     * @return string
     * @throws RaporException
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
                throw new \Exception("File tidak berhasil disimpan");
            }
            
            Log::info('Rapor berhasil disimpan:', [
                'path' => $outputPath,
                'size' => filesize($outputPath),
                'tahun_ajaran_id' => $this->tahunAjaranId
            ]);
            
            return "generated/{$filename}";
        } catch (\Exception $e) {
            Log::error('Error saat menyimpan file rapor:', [
                'error' => $e->getMessage(),
                'path' => $outputPath,
                'tahun_ajaran_id' => $this->tahunAjaranId
            ]);
            
            throw new RaporException(
                "Gagal menyimpan file rapor: " . $e->getMessage(),
                'file_processing',
                self::ERROR_FILE_PROCESSING
            );
        }
    }
}