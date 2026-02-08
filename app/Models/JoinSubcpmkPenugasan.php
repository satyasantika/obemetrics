<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinSubcpmkPenugasan extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function subcpmk(): BelongsTo
    {
        return $this->belongsTo(Subcpmk::class);
    }

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

}
