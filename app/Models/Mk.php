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

    public function joinCplMks(): HasMany
    {
        return $this->hasMany(JoinCplMk::class);
    }

    public function joinMkUsers(): HasMany
    {
        return $this->hasMany(JoinMkUser::class);
    }

    public function cpmks(): HasMany
    {
        return $this->hasMany(Cpmk::class);
    }

    public function subcpmks(): HasMany
    {
        return $this->hasMany(Subcpmk::class);
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

    public function getDosenPengampuAttribute(): ?User
    {
        if ($this->relationLoaded('joinMkUsers')) {
            $fromLoadedRelation = $this->joinMkUsers
                ->sortByDesc(fn ($row) => (int) ($row->koordinator ?? 0))
                ->map(fn ($row) => $row->user)
                ->filter()
                ->first();

            if ($fromLoadedRelation instanceof User) {
                return $fromLoadedRelation;
            }
        }

        return $this->joinMkUsers()
            ->with('user')
            ->orderByDesc('koordinator')
            ->get()
            ->map(fn ($row) => $row->user)
            ->filter()
            ->first();
    }
}
