<?php

namespace App\Models;

use App\Models\ProfilCpl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cpl extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kurikulums(): BelongsToMany
    {
        return $this->belongsToMany(Kurikulum::class, 'kurikulum_cpls')
            ->withPivot('kode_cpl')
            ->withTimestamps();
    }

    public function getKodeAttribute(): ?string
    {
        if (isset($this->pivot) && isset($this->pivot->kode_cpl)) {
            return $this->pivot->kode_cpl;
        }
        return $this->attributes['kode'] ?? null;
    }

    public function profilCpls(): HasMany
    {
        return $this->hasMany(ProfilCpl::class);
    }

    public function joinCplBks(): HasMany
    {
        return $this->hasMany(JoinCplBk::class);
    }

}
