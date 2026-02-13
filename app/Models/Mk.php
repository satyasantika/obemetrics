<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function joinBkMks(): HasMany
    {
        return $this->hasMany(JoinBkMk::class);
    }

    public function joinMkUsers(): HasMany
    {
        return $this->hasMany(JoinMkUser::class);
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(Cpmk::class);
    }

    public function joinCplCpmks(): HasMany
    {
        return $this->hasMany(JoinCplCpmk::class);
    }

    public function penugasans(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }

    public function joinSubcpmkPenugasans(): HasMany
    {
        return $this->hasMany(JoinSubcpmkPenugasan::class);
    }

    public function kontrakMks(): HasMany
    {
        return $this->hasMany(KontrakMk::class);
    }

    public function nilais(): HasMany
    {
        return $this->hasMany(Nilai::class);
    }
}
