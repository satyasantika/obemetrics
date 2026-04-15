<?php

namespace App\Models;

use App\Models\CplMk;
use App\Models\KurikulumMk;
use App\States\Mk\MkState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStates\HasStates;

class Mk extends Model
{
    use HasFactory, HasUuids, HasStates;
    protected $guarded = ['id'];

    protected $casts = [
        'status' => MkState::class,
    ];

    public function kurikulums(): BelongsToMany
    {
        return $this->belongsToMany(Kurikulum::class, 'kurikulum_mks')
            ->withPivot('kode_mk', 'semester_ke')
            ->withTimestamps();
    }
    /**
     * Akses semester_ke dari pivot (kurikulum_mks)
     */
    public function getSemesterKeAttribute(): ?int
    {
        if (isset($this->pivot) && isset($this->pivot->semester_ke)) {
            return $this->pivot->semester_ke;
        }
        // fallback jika tidak ada pivot
        return null;
    }

    public function kurikulumMks(): HasMany
    {
        return $this->hasMany(KurikulumMk::class);
    }

    public function kurikulum(): HasOneThrough
    {
        return $this->hasOneThrough(Kurikulum::class, KurikulumMk::class, 'mk_id', 'id', 'id', 'kurikulum_id');
    }

    public function getKodeAttribute(): ?string
    {
        if (isset($this->pivot) && isset($this->pivot->kode_mk)) {
            return $this->pivot->kode_mk;
        }

        return $this->attributes['kode'] ?? null;
    }

    public function getKurikulumIdAttribute(): ?string
    {
        if (array_key_exists('kurikulum_id', $this->attributes)) {
            return $this->attributes['kurikulum_id'];
        }

        if (isset($this->pivot) && isset($this->pivot->kurikulum_id)) {
            return $this->pivot->kurikulum_id;
        }

        return $this->kurikulumMks()->value('kurikulum_id');
    }

    public function cplMks(): HasMany
    {
        return $this->hasMany(CplMk::class);
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
