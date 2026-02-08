<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subcpmk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function joinCplCpmk(): BelongsTo
    {
        return $this->belongsTo(JoinCplCpmk::class);
    }

    public function pertemuans(): HasMany
    {
        return $this->hasMany(Pertemuan::class);
    }

    public function joinSubcpmkPenugasans(): HasMany
    {
        return $this->hasMany(JoinSubcpmkPenugasan::class);
    }
}
