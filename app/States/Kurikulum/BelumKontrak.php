<?php

namespace App\States\Kurikulum;

class BelumKontrak extends KurikulumState
{
    public static $name = 'belum_kontrak';

    public function label(): string
    {
        return 'Bobot Lengkap';
    }
}
