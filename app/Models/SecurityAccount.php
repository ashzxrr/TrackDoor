<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['username', 'password'])]
#[Hidden(['password'])]
class SecurityAccount extends Model
{
    protected $table = 'security_account';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
