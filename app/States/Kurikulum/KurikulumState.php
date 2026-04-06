<?php

namespace App\States\Kurikulum;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class KurikulumState extends State
{
    abstract public function label(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Draft::class)
            // maju
            ->allowTransition(Draft::class, BelumInteraksi::class)
            ->allowTransition(BelumInteraksi::class, BelumBobot::class)
            ->allowTransition(BelumBobot::class, BelumKontrak::class)
            ->allowTransition(BelumKontrak::class, Aktif::class)
            // mundur (jika data dihapus)
            ->allowTransition(BelumInteraksi::class, Draft::class)
            ->allowTransition(BelumBobot::class, BelumInteraksi::class)
            ->allowTransition(BelumKontrak::class, BelumBobot::class)
            ->allowTransition(Aktif::class, BelumKontrak::class)
            // nonaktif
            ->allowTransition(Aktif::class, NonAktif::class)
            ->allowTransition(NonAktif::class, Aktif::class);
    }
}
