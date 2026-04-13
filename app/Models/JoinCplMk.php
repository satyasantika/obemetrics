<?php

namespace App\Models;

use App\Models\CplBk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JoinCplMk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function cplBk(): BelongsTo
    {
        return $this->belongsTo(CplBk::class);
    }

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }
}
