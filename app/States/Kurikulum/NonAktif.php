<?php

namespace App\States\Kurikulum;

class NonAktif extends KurikulumState
{
    public static $name = 'non_aktif';

    public function label(): string
    {
        return 'Non Aktif';
    }
}
