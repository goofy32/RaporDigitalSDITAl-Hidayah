<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nomor_kelas',
        'nama_kelas',
    ];

    protected $appends = ['full_kelas'];
    
    protected $casts = [
        'nomor_kelas' => 'integer'
    ];

    // Relasi dengan guru
    public function guru()
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->withPivot('is_wali_kelas', 'role')
            ->withTimestamps();
    }
    // Wali kelas
    public function waliKelas()
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->withPivot('role');
    }

    public function getProgressKelas()
    {
        $total_siswa = $this->siswas()->count();
        $completed_siswa = $this->siswas()
            ->whereHas('nilais', function($q) {
                $q->whereNotNull('nilai_akhir_rapor');
            })->count();
        
        return [
            'total' => $total_siswa,
            'completed' => $completed_siswa,
            'percentage' => $total_siswa > 0 ? ($completed_siswa / $total_siswa) * 100 : 0
        ];
    }

    // Guru pengajar
    public function guruPengajar()
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->wherePivot('role', 'pengajar')
            ->withTimestamps();
    }

    public function getWaliKelas()
    {
        // Ambil data wali kelas untuk kelas ini
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->first();
    }

    // Get wali kelas name
    public function getWaliKelasNameAttribute()
    {
        $waliKelas = $this->waliKelas()->first();
        return $waliKelas ? $waliKelas->nama : '-';
    }

    // Get full kelas name
    public function getFullKelasAttribute()
    {
        return "Kelas {$this->nomor_kelas} {$this->nama_kelas}";
    }
    
    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }

    public function mataPelajarans()
    {
        return $this->hasMany(MataPelajaran::class, 'kelas_id');
    }

    public function isWaliKelas($guruId)
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->where('guru_kelas.guru_id', $guruId)
            ->exists();
    }

    public function getWaliKelasId()
    {
        $waliKelas = $this->getWaliKelas();
        return $waliKelas ? $waliKelas->id : null;
    }
    
    public static function getWaliKelasMap()
    {
        $result = [];
        $allKelas = self::all();
        
        foreach ($allKelas as $kelas) {
            $waliKelasId = $kelas->getWaliKelasId();
            if ($waliKelasId) {
                $result[$kelas->id] = $waliKelasId;
            }
        }
        
        return json_encode($result);
    }

    public function toArray()
    {
        $array = parent::toArray();
        $array['mata_pelajarans'] = $this->mataPelajarans;
        return $array;
    }

    public function hasWaliKelas()
    {
        // Periksa apakah kelas sudah memiliki wali kelas
        return $this->belongsToMany(Guru::class, 'guru_kelas')
            ->where('guru_kelas.is_wali_kelas', true)
            ->where('guru_kelas.role', 'wali_kelas')
            ->exists();
    }

    /**
     * Ambil wali kelas dengan format yang mudah digunakan untuk JavaScript
     * 
     * @return array
     */
    public static function getWaliKelasMapping()
    {
        $result = [];
        $allKelas = self::all();
        
        foreach ($allKelas as $kelas) {
            $waliKelasId = $kelas->getWaliKelasId();
            if ($waliKelasId) {
                $result[$kelas->id] = [
                    'id' => $waliKelasId,
                    'kelas' => "Kelas {$kelas->nomor_kelas} {$kelas->nama_kelas}",
                    'nama' => optional($kelas->getWaliKelas())->nama
                ];
            }
        }
        
        return $result;
    }
}