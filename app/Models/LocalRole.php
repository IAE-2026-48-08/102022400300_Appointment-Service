<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalRole extends Model
{
    protected $fillable = [
        'sso_email',
        'sso_role',
        'local_role',
    ];
}