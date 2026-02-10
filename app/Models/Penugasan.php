<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penugasan extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function evaluasi()
    {
        return $this->belongsTo(Evaluasi::class);
    }

    public function joinSubcpmkPenugasans(): HasMany
    {
        return $this->hasMany(JoinSubcpmkPenugasan::class);
    }

}
