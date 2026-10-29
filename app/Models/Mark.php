<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mark extends Model
{
    protected $fillable = ['name', 'type'];

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}