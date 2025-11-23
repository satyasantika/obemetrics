<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prodi extends Model
{
    use HasFactory, HasUuids;
    protected $guarded = ['id'];


    // public function users(): hasMany
    // {
    //     return $this->hasMany(User::class);
    // }

    // public function profils(): hasMany
    // {
    //     return $this->hasMany(Profil::class);
    // }

    // public function kurikulums()
    // {
    //     return $this->hasMany(Kurikulum::class);
    // }

}
