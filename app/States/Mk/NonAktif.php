<?php

namespace App\States\Mk;

class NonAktif extends MkState
{
    public static $name = 'non_aktif';

    public function label(): string
    {
        return 'Non Aktif';
    }
}
