<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cpl extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function joinProfilCpls(): HasMany
    {
        return $this->hasMany(JoinProfilCpl::class);
    }

    public function joinCplBks(): HasMany
    {
        return $this->hasMany(JoinCplBk::class);
    }

}
