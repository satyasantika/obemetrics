<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinBkMk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function bk(): BelongsTo
    {
        return $this->belongsTo(Bk::class);
    }

    public function joinCplCpmk() : HasMany
    {
        return $this->hasMany(JoinCplCpmk::class);
    }

}
