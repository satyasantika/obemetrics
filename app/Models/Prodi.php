<?php

namespace App\Models;

use App\States\Prodi\ProdiState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\ModelStates\HasStates;

class Prodi extends Model
{
    use HasFactory, HasUuids, HasStates;
    protected $guarded = ['id'];

    protected $casts = [
        'status' => ProdiState::class,
    ];

    public function kurikulums(): HasMany
    {
        return $this->hasMany(Kurikulum::class);
    }

    public function mahasiswas(): HasMany
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function joinProdiUsers(): HasMany
    {
        return $this->hasMany(JoinProdiUser::class);
    }

}
