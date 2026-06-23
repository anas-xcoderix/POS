<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Franchise extends Model
{
    protected $fillable = ['code', 'name', 'name_ar', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

}
