<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class Siswa extends Model
{
    use HasFactory, HasTahunAjaran;

    protected $table = 'siswas';

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'kelas_id',
        'nama_ayah',
        'nama_ibu',
        'pekerjaan_ayah',
        'pekerjaan_ibu',
        'alamat_orangtua',
        'photo',
        'wali_siswa',
        'pekerjaan_wali',
        'tahun_ajaran_id'
    ];
    public function kelas()
    {
        return $this->belongsTo(Kelas::class)->orderBy('nomor_kelas', 'asc');        
    }

    public function getTahunAjaranIdAttribute()
    {
        return $this->kelas->tahun_ajaran_id ?? session('tahun_ajaran_id');
    }

    public function tahunAjaran()
    {
        if ($this->kelas) {
            return $this->kelas->tahunAjaran();
        }
        
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }
    
    public function scopeTahunAjaran($query, $tahunAjaranId)
    {
        return $query->whereHas('kelas', function($q) use ($tahunAjaranId) {
            $q->where('tahun_ajaran_id', $tahunAjaranId);
        });
    }
    
    public function scopeAktif($query)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        if ($tahunAjaranAktif) {
            return $this->scopeTahunAjaran($query, $tahunAjaranAktif->id);
        }
        return $query;
    }

    public function prestasi()
    {
        return $this->hasMany(Prestasi::class);
    }
    public function nilais()
    {
        return $this->hasMany(Nilai::class);
    }

    public function nilaiEkstrakurikuler()
    {
        return $this->hasMany(NilaiEkstrakurikuler::class);
    }

    public function absensi()
    {
        return $this->hasOne(Absensi::class);
    }

    public function getKelasLengkapAttribute()
    {
        return $this->kelas ? $this->kelas->full_kelas : '-';
    }

    public static function debugWaliKelasRelation($guruId, $kelasId)
    {
        // Helper untuk debugging relasi wali kelas
        $guru = \App\Models\Guru::find($guruId);
        $kelas = \App\Models\Kelas::find($kelasId);
        
        if (!$guru || !$kelas) {
            return [
                'success' => false,
                'message' => 'Guru atau kelas tidak ditemukan'
            ];
        }
        
        // Cek pivot table
        $pivot = \DB::table('guru_kelas')
            ->where('guru_id', $guruId)
            ->where('kelas_id', $kelasId)
            ->first();
        
        return [
            'success' => true,
            'guru' => $guru->toArray(),
            'kelas' => $kelas->toArray(),
            'pivot' => $pivot ? (array)$pivot : null,
            'is_wali_kelas' => $pivot ? ($pivot->is_wali_kelas && $pivot->role === 'wali_kelas') : false
        ];
    }

    // Tambahkan scope untuk filter berdasarkan wali kelas
    public function scopeWaliKelas($query, $guruId)
    {
        return $query->whereHas('kelas', function($q) use ($guruId) {
            $q->where('wali_kelas', $guruId);
        });
    }

    /**
     * Periksa apakah siswa berada di kelas yang diwalikan oleh guru
     * 
     * @param int $guruId
     * @return bool
     */
    public function isInKelasWali($guruId)
    {
        // Log untuk debugging
        \Log::info('Checking isInKelasWali', [
            'siswa_id' => $this->id,
            'kelas_id' => $this->kelas_id,
            'guru_id' => $guruId
        ]);
        
        // Cek jika siswa memiliki kelas
        if (!$this->kelas) {
            \Log::warning('Siswa tidak memiliki kelas', ['siswa_id' => $this->id]);
            return false;
        }
        
        // Cari guru yang bersangkutan
        $guru = \App\Models\Guru::find($guruId);
        if (!$guru) {
            \Log::warning('Guru tidak ditemukan', ['guru_id' => $guruId]);
            return false;
        }
        
        // Gunakan method manual yang baru dibuat
        $kelasWaliIds = \DB::table('guru_kelas')
            ->where('guru_id', $guruId)
            ->where('is_wali_kelas', true)
            ->where('role', 'wali_kelas')
            ->pluck('kelas_id');
        
        // Periksa apakah kelas siswa termasuk dalam kelas-kelas yang diwalikan
        $result = $kelasWaliIds->contains($this->kelas_id);
        
        \Log::info('isInKelasWali result', [
            'siswa_kelas_id' => $this->kelas_id,
            'wali_kelas_ids' => $kelasWaliIds->toArray(),
            'is_match' => $result
        ]);
        
        return $result;
    }

    public function hasCompleteData($type = 'UTS')
    {
        $semester = $type === 'UTS' ? 1 : 2;
        $tahunAjaranId = session('tahun_ajaran_id');
        
        // Cek nilai berdasarkan semester dan tahun ajaran
        $hasNilai = $this->nilais()
            ->whereHas('mataPelajaran', function($q) use ($semester) {
                $q->where('semester', $semester);
            })
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->where('nilai_akhir_rapor', '!=', null)
            ->exists();
        
        // Cek kehadiran semester yang sesuai dan tahun ajaran
        $hasAbsensi = $this->absensi()
            ->where('semester', $semester)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->exists();
        
        return $hasNilai && $hasAbsensi;
    }
}