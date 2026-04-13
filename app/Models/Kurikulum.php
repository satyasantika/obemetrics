<?php

namespace App\Models;

use App\Models\KurikulumCpl;
use App\Models\ProfilCpl;
use App\States\Kurikulum\KurikulumState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStates\HasStates;

class Kurikulum extends Model
{
    use HasFactory, HasUuids, HasStates;
    protected $guarded = ['id'];
    protected $casts = [
        'status_aktif' => 'boolean',
        'status' => KurikulumState::class,
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function profils(): HasMany
    {
        return $this->hasMany(Profil::class);
    }

    public function cpls(): BelongsToMany
    {
        return $this->belongsToMany(Cpl::class, 'kurikulum_cpls')
            ->withTimestamps();
    }

    public function kurikulumCpls(): HasMany
    {
        return $this->hasMany(KurikulumCpl::class);
    }

    public function bks(): HasMany
    {
        return $this->hasMany(Bk::class);
    }

    public function mks(): HasMany
    {
        return $this->hasMany(Mk::class);
    }

    public function profilCpls(): HasMany
    {
        return $this->hasMany(ProfilCpl::class);
    }

    public function joinCplBks(): HasMany
    {
        return $this->hasMany(JoinCplBk::class);
    }

    public function joinCplMks(): HasMany
    {
        return $this->hasMany(JoinCplMk::class);
    }

    public function joinMkUsers(): HasMany
    {
        return $this->hasMany(JoinMkUser::class);
    }

    public function kontrakMks(): HasManyThrough
    {
        return $this->hasManyThrough(KontrakMk::class, Mk::class);
    }

}
