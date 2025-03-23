<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilSekolah extends Model
{
    use HasFactory;

    protected $table = 'profil_sekolah';

    protected $fillable = [
        'logo',
        'nama_instansi',
        'nama_sekolah',
        'tahun_pelajaran',
        'semester',
        'npsn',
        'kepala_sekolah',
        'nip_kepala_sekolah',
        'alamat',
        'guru_kelas',
        'kode_pos',
        'kelas',
        'telepon',
        'jumlah_siswa',
        'email_sekolah',
        'tempat_terbit',
        'tanggal_terbit',
        'website',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'nip_wali_kelas'
    ];

    /**
     * Sync current school profile with active TahunAjaran
     */
    public static function syncWithTahunAjaran()
    {
        $profil = self::first();
        if (!$profil) return;

        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (!$tahunAjaran) return;

        $profil->update([
            'tahun_pelajaran' => $tahunAjaran->tahun_ajaran,
            'semester' => $tahunAjaran->semester
        ]);
    }
    
    /**
     * Relasi dengan TahunAjaran aktif
     */
    public function tahunAjaranAktif()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_pelajaran', 'tahun_ajaran');
    }
    
    /**
     * Get tahun ajaran dari profil atau default dari model TahunAjaran
     */
    public function getTahunAjaranAttribute()
    {
        if (!$this->tahun_pelajaran) {
            // Jika tidak ada di profil, ambil dari model TahunAjaran yang aktif
            $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            return $tahunAjaran ? $tahunAjaran->tahun_ajaran : date('Y') . '/' . (date('Y') + 1);
        }
        
        return $this->tahun_pelajaran;
    }
    
    /**
     * Get semester dari profil atau default dari model TahunAjaran
     */
    public function getSemesterAttribute($value)
    {
        if (!$value) {
            // Jika tidak ada di profil, ambil dari model TahunAjaran yang aktif
            $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            return $tahunAjaran ? $tahunAjaran->semester : 1;
        }
        
        return $value;
    }
}