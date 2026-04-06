<?php

namespace App\States\Prodi;

class NonAktif extends ProdiState
{
    public static $name = 'non_aktif';

    public function label(): string
    {
        return 'Non Aktif';
    }
}
