<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinCplCpmk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function joinCplBk(): BelongsTo
    {
        return $this->belongsTo(JoinCplBk::class);
    }

    public function cpmk(): BelongsTo
    {
        return $this->belongsTo(Cpmk::class);
    }

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function subcpmks()
    {
        return $this->hasMany(Subcpmk::class);
    }

}
