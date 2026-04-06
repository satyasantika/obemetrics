<?php

namespace App\States\Kurikulum;

class Aktif extends KurikulumState
{
    public static $name = 'aktif';

    public function label(): string
    {
        return 'Aktif';
    }
}
