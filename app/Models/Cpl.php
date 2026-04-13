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
            ->withTimestamps();
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
