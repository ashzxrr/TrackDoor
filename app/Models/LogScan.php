<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['karyawan_id', 'gedung_id', 'tipe', 'alasan_id', 'scanned_at'])]
class LogScan extends Model
{
    protected $table = 'log_scan';

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function gedung(): BelongsTo
    {
        return $this->belongsTo(Gedung::class);
    }

    public function alasan(): BelongsTo
    {
        return $this->belongsTo(AlasanKeluar::class, 'alasan_id');
    }
}
