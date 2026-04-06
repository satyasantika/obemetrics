<?php

namespace App\States\Mk;

class Aktif extends MkState
{
    public static $name = 'aktif';

    public function label(): string
    {
        return 'Lengkap';
    }
}
