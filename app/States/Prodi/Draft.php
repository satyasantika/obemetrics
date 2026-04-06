<?php

namespace App\States\Prodi;

class Draft extends ProdiState
{
    public static $name = 'draft';

    public function label(): string
    {
        return 'Belum Set User';
    }
}
