<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyImportMap extends Model
{
    protected $fillable = [
        'entity_type', 'legacy_key', 'local_id', 'legacy_table',
    ];
}
