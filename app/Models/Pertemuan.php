<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pertemuan extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function subcpmk(): BelongsTo
    {
        return $this->belongsTo(SubCpmk::class);
    }

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function joinPertemuanMetodes(): HasMany
    {
        return $this->hasMany(JoinPertemuanMetode::class);
    }

}
