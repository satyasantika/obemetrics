<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JoinMkUser extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function mk(): BelongsTo
    {
        return $this->belongsTo(Mk::class);
    }

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

}
