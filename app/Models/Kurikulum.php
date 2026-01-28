<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kurikulum extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];
    protected $casts = [
        'status_aktif' => 'boolean',
    ];

    public function prodi(): BelongsTo
    {
        return $this->belongsTo(Prodi::class);
    }

    public function profils(): HasMany
    {
        return $this->hasMany(Profil::class);
    }

    public function cpls(): HasMany
    {
        return $this->hasMany(Cpl::class);
    }

    public function bks(): HasMany
    {
        return $this->hasMany(Bk::class);
    }

    public function mks(): HasMany
    {
        return $this->hasMany(Mk::class);
    }

    public function joinProfilCpls(): HasMany
    {
        return $this->hasMany(JoinProfilCpl::class);
    }

    public function joinCplBks(): HasMany
    {
        return $this->hasMany(JoinCplBk::class);
    }

    public function joinBkMks(): HasMany
    {
        return $this->hasMany(JoinBkMk::class);
    }

}
