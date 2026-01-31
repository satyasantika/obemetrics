<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinCplBk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function bk(): BelongsTo
    {
        return $this->belongsTo(Bk::class);
    }

    public function joinCplBk() : HasMany
    {
        return $this->hasMany(JoinCplBk::class);
    }

}
