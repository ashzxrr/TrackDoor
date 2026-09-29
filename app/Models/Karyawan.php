<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nip', 'nama', 'bagian', 'barcode_value', 'foto_path'])]
class Karyawan extends Model
{
    protected $table = 'karyawan';

    public function logScans(): HasMany
    {
        return $this->hasMany(LogScan::class);
    }
}
