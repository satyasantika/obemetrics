<?php

namespace App\States\Mk;

class Draft extends MkState
{
    public static $name = 'draft';

    public function label(): string
    {
        return 'Draft';
    }
}
