<?php

namespace App\Models;

use App\Models\Cpl;
use App\Models\Kurikulum;
use App\Models\ProfilCpl;
use App\Models\ProfilIndikator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profil extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function profil_indikators(): HasMany
    {
        return $this->hasMany(ProfilIndikator::class);
    }

    public function profilCpls(): HasMany
    {
        return $this->hasMany(ProfilCpl::class);
    }

    public function cpls(): HasManyThrough
    {
        return $this->hasManyThrough(Cpl::class, ProfilCpl::class, 'profil_id', 'id', 'id', 'cpl_id');
    }

}
