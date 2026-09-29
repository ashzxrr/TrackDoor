<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['username', 'password'])]
#[Hidden(['password'])]
class AdminUser extends Model
{
    protected $table = 'admin_users';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
