<?php

namespace App\States\Mk;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class MkState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            ->allowTransition(Draft::class, MappingSubCPMK::class)
            ->allowTransition(MappingSubCPMK::class, Draft::class)
            ->allowTransition(MappingSubCPMK::class, BelumNilai::class)
            ->allowTransition(BelumNilai::class, MappingSubCPMK::class)
            ->allowTransition(BelumNilai::class, Aktif::class)
            ->allowTransition(Aktif::class, MappingSubCPMK::class)
            ->allowTransition(Aktif::class, BelumNilai::class)
            ->allowTransition(Aktif::class, Draft::class)
            ->allowTransition(Aktif::class, NonAktif::class)
            ->allowTransition(NonAktif::class, Aktif::class);
    }
}
