<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasTahunAjaran;

class BobotNilai extends Model
{
    use HasTahunAjaran;
    
    protected $fillable = [
        'tahun_ajaran_id',
        'bobot_tp',
        'bobot_lm',
        'bobot_as'
    ];
    
    public static function getDefault()
    {
        $tahunAjaranId = session('tahun_ajaran_id');
        $default = self::where('tahun_ajaran_id', $tahunAjaranId)->first();
        
        if (!$default) {
            $default = self::create([
                'tahun_ajaran_id' => $tahunAjaranId,
                'bobot_tp' => 0.25,
                'bobot_lm' => 0.25,
                'bobot_as' => 0.50
            ]);
        }
        
        return $default;
    }
}
