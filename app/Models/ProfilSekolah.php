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
}