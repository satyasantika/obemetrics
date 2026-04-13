<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KurikulumBk extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'kurikulum_bks';

    protected $guarded = ['id'];

    public function kurikulum(): BelongsTo
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function bk(): BelongsTo
    {
        return $this->belongsTo(Bk::class);
    }
}
