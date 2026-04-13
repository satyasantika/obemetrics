<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CplBk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];
    protected $table = 'cpl_bks';

    public function cpl(): BelongsTo
    {
        return $this->belongsTo(Cpl::class);
    }

    public function bk(): BelongsTo
    {
        return $this->belongsTo(Bk::class);
    }

    public function joinCplCpmks(): HasMany
    {
        return $this->hasMany(JoinCplCpmk::class);
    }
}
