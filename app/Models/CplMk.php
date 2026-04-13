<?php

namespace App\Models;

use App\Models\CplBk;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CplMk extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cpl_mks';

    protected $guarded = ['id'];

    public function cplBk(): BelongsTo
    {
        return $this->belongsTo(CplBk::class);
    }

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }
}
