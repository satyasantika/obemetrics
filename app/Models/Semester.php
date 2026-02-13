<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semester extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kontrakmks(): HasMany
    {
        return $this->hasMany(KontrakMk::class);
    }

}
