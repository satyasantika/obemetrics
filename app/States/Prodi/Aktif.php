<?php

namespace App\States\Prodi;

class Aktif extends ProdiState
{
    public static $name = 'aktif';

    public function label(): string
    {
        return 'Aktif';
    }
}
