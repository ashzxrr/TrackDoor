<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label'])]
class AlasanKeluar extends Model
{
    protected $table = 'alasan_keluar';
}
