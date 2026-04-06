<?php

namespace App\States\Kurikulum;

class Draft extends KurikulumState
{
    public static $name = 'draft';

    public function label(): string
    {
        return 'Draft';
    }
}
