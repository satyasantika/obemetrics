<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KontrakMk extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function mk()
    {
        return $this->belongsTo(Mk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class); // dosen pengampu
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
