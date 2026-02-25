<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cpmk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function subcpmks(): HasManyThrough
    {
        return $this->hasManyThrough(
            Subcpmk::class,
            JoinCplCpmk::class,
            'cpmk_id',
            'join_cpl_cpmk_id',
            'id',
            'id'
        );
    }

    public function joinCplCpmks(): HasMany
    {
        return $this->hasMany(JoinCplCpmk::class);
    }

}
