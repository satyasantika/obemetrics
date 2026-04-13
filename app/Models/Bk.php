<?php

namespace App\Models;

use App\Models\KurikulumBk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kurikulums(): BelongsToMany
    {
        return $this->belongsToMany(Kurikulum::class, 'kurikulum_bks')
            ->withPivot('kode_bk')
            ->withTimestamps();
    }

    public function getKodeAttribute(): ?string
    {
        if (isset($this->pivot) && isset($this->pivot->kode_bk)) {
            return $this->pivot->kode_bk;
        }
        return $this->attributes['kode'] ?? null;
    }

    public function kurikulumBks(): HasMany
    {
        return $this->hasMany(KurikulumBk::class);
    }

    public function joinCplBks(): HasMany
    {
        return $this->hasMany(CplBk::class);
    }

    public function joinCplMks(): HasManyThrough
    {
        return $this->hasManyThrough(
            JoinCplMk::class,
            CplBk::class,
            'bk_id',
            'cpl_bk_id',
            'id',
            'id'
        );
    }

}
