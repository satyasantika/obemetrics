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
            ->allowTransition(Draft::class, BelumBobot::class)
            ->allowTransition(Draft::class, BelumKontrak::class)
            ->allowTransition(Draft::class, Aktif::class)
            ->allowTransition(BelumInteraksi::class, BelumBobot::class)
            ->allowTransition(BelumInteraksi::class, BelumKontrak::class)
            ->allowTransition(BelumInteraksi::class, Aktif::class)
            ->allowTransition(BelumBobot::class, BelumKontrak::class)
            ->allowTransition(BelumBobot::class, Aktif::class)
            ->allowTransition(BelumKontrak::class, Aktif::class)
            // mundur (jika data dihapus)
            ->allowTransition(BelumInteraksi::class, Draft::class)
            ->allowTransition(BelumBobot::class, BelumInteraksi::class)
            ->allowTransition(BelumBobot::class, Draft::class)
            ->allowTransition(BelumKontrak::class, BelumBobot::class)
            ->allowTransition(BelumKontrak::class, BelumInteraksi::class)
            ->allowTransition(BelumKontrak::class, Draft::class)
            ->allowTransition(Aktif::class, BelumKontrak::class)
            ->allowTransition(Aktif::class, BelumBobot::class)
            ->allowTransition(Aktif::class, BelumInteraksi::class)
            ->allowTransition(Aktif::class, Draft::class)
            // nonaktif
            ->allowTransition(Aktif::class, NonAktif::class)
            ->allowTransition(NonAktif::class, Aktif::class);
    }
}
