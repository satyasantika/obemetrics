<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinPertemuanMetode extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function metode(): BelongsTo
    {
        return $this->belongsTo(Metode::class);
    }

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

}
