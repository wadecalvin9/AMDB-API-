<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $fillable = ['manifest_url', 'name', 'manifest'];

    protected $casts = [
        'manifest' => 'array',
    ];
}
