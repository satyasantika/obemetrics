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
use Spatie\ModelStates\Exceptions\TransitionNotFound;

class SyncKurikulumState
{
    // Urutan state dari paling awal ke paling lengkap
    private const STATE_ORDER = [
        Draft::class,
        BelumInteraksi::class,
        BelumBobot::class,
        BelumKontrak::class,
        Aktif::class,
    ];

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

        $interaksiComplete = $kurikulum->joinProfilCpls()->exists()
            && $kurikulum->joinCplBks()->exists();

        $mkIds         = $kurikulum->mks()->pluck('id');
        $bobotedMkIds  = $kurikulum->joinCplMks()->distinct()->pluck('mk_id');
        $bobotComplete = $mkIds->isNotEmpty() && $mkIds->diff($bobotedMkIds)->isEmpty();

        $kontrakComplete = $kurikulum->kontrakMks()->exists();

        $target = match (true) {
            $dataComplete && $interaksiComplete && $bobotComplete && $kontrakComplete => Aktif::class,
            $dataComplete && $interaksiComplete && $bobotComplete                    => BelumKontrak::class,
            $dataComplete && $interaksiComplete                                      => BelumBobot::class,
            $dataComplete                                                            => BelumInteraksi::class,
            default                                                                  => Draft::class,
        };

        if ($kurikulum->status instanceof $target) {
            return;
        }

        // Transisi step-by-step agar tidak melompati state yang tidak terdaftar
        $currentIndex = array_search(get_class($kurikulum->status), self::STATE_ORDER);
        $targetIndex  = array_search($target, self::STATE_ORDER);

        if ($currentIndex === false || $targetIndex === false) {
            return;
        }

        try {
            while ($currentIndex !== $targetIndex) {
                $nextIndex = $currentIndex < $targetIndex
                    ? $currentIndex + 1
                    : $currentIndex - 1;

                $kurikulum->status->transitionTo(self::STATE_ORDER[$nextIndex]);
                $kurikulum->refresh();
                $currentIndex = $nextIndex;
            }
        } catch (TransitionNotAllowed | TransitionNotFound $e) {
            // Transisi tidak dapat dilanjutkan; berhenti di state saat ini
        }
    }
}
