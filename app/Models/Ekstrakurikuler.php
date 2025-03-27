<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class Ekstrakurikuler extends Model
{
    use HasFactory, HasTahunAjaran;

    protected $table = 'ekstrakurikulers';

    protected $fillable = [
        'nama_ekstrakurikuler',
        'pembina',
        'tahun_ajaran_id',
    ];
}