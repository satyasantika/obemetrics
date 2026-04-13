<?php

namespace App\Actions;

use App\Models\Kurikulum;
use App\States\Kurikulum\Aktif;
use App\States\Kurikulum\BelumBobot;
use App\States\Kurikulum\BelumInteraksi;
use App\States\Kurikulum\BelumKontrak;
use App\States\Kurikulum\Draft;
use App\States\Kurikulum\NonAktif;
use Spatie\ModelStates\Exceptions\TransitionNotAllowed;

class SyncKurikulumState
{
    public static function sync(Kurikulum $kurikulum): void
    {
        // Jangan ubah state NonAktif secara otomatis — hanya via aksi manual
        if ($kurikulum->status instanceof NonAktif) {
            return;
        }

        $dataComplete = $kurikulum->profils()->exists()
            && $kurikulum->cpls()->exists()
            && $kurikulum->bks()->exists()
            && $kurikulum->mks()->exists()
            && $kurikulum->joinMkUsers()->exists();

        $interaksiComplete = $kurikulum->profilCpls()->exists()
            && $kurikulum->joinCplBks()->exists();

        $mkIds         = $kurikulum->mks()->pluck('mks.id');
        $bobotedMkIds  = $kurikulum->joinCplMks()->distinct()->pluck('join_cpl_mks.mk_id');
        $kontrakMkIds  = $kurikulum->kontrakMks()->distinct()->pluck('kontrak_mks.mk_id');

        $bobotComplete = $mkIds->isNotEmpty() && $mkIds->diff($bobotedMkIds)->isEmpty();
        $kontrakComplete = $mkIds->isNotEmpty() && $mkIds->diff($kontrakMkIds)->isEmpty();

        $target = match (true) {
            $dataComplete && $interaksiComplete && $bobotComplete && $kontrakComplete => Aktif::class,
            $dataComplete && $interaksiComplete && $bobotComplete                    => BelumKontrak::class,
            $dataComplete && $interaksiComplete                                      => BelumBobot::class,
            $dataComplete                                                            => BelumInteraksi::class,
            default                                                                  => Draft::class,
        };

        if (!($kurikulum->status instanceof $target)) {
            try {
                $kurikulum->status->transitionTo($target);
            } catch (TransitionNotAllowed $e) {
                // Transisi tidak diizinkan dari state saat ini; abaikan
            }
        }
    }
}
