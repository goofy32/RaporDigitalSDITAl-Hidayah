<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class NotificationRead extends Pivot
{
    protected $table = 'notification_reads';

    protected $casts = [
        'read_at' => 'datetime'
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}