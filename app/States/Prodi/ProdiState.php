<?php

namespace App\States\Prodi;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class ProdiState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, Aktif::class)
            ->allowTransition(Aktif::class, Draft::class)
            ->allowTransition(Aktif::class, NonAktif::class)
            ->allowTransition(NonAktif::class, Aktif::class);
    }
}
