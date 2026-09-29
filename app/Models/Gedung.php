<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_gedung'])]
class Gedung extends Model
{
    protected $table = 'gedung';
}
