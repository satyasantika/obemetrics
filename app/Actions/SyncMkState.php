<?php

namespace App\Actions;

use App\Models\Mk;
use App\States\Mk\Aktif;
use App\States\Mk\BelumNilai;
use App\States\Mk\Draft;
use App\States\Mk\MappingSubCPMK;
use App\States\Mk\NonAktif;
use Spatie\ModelStates\Exceptions\TransitionNotAllowed;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

class SyncMkState
{
    // Urutan state dari paling awal ke paling lengkap
    private const STATE_ORDER = [
        Draft::class,
        MappingSubCPMK::class,
        BelumNilai::class,
        Aktif::class,
    ];

    public static function sync(Mk $mk): void
    {
        // Jangan ubah state NonAktif secara otomatis — hanya via aksi manual
        if ($mk->status instanceof NonAktif) {
            return;
        }

        // Draft: belum ada CPMK, atau belum ada SubCPMK via joinCplCpmks, atau belum ada Penugasan
        $cpmkExists           = $mk->cpmks()->exists();
        $joinCplCpmkExists    = $mk->joinCplCpmks()->exists();
        $subcpmkExists        = $mk->joinCplCpmks()->whereHas('subcpmks')->exists();
        $penugasanExists      = $mk->penugasans()->exists();

        $dataComplete = $cpmkExists && $joinCplCpmkExists && $subcpmkExists && $penugasanExists;

        // MappingSubCPMK: data lengkap tapi belum ada mapping SubCPMK ↔ Penugasan
        $mappingExists = $mk->joinSubcpmkPenugasans()->exists();

        // BelumNilai: mapping sudah ada, tapi nilai belum masuk semua
        // Semua kombinasi kontrak_mk x penugasan harus punya entri nilai
        $nilaiComplete = false;
        if ($mappingExists) {
            $kontrakCount  = $mk->kontrakMks()->count();
            $penugasanCount = $mk->penugasans()->count();
            $expectedNilai = $kontrakCount * $penugasanCount;
            $actualNilai   = $mk->nilais()->count();

            $nilaiComplete = $expectedNilai > 0 && $actualNilai >= $expectedNilai;
        }

        $target = match (true) {
            $dataComplete && $mappingExists && $nilaiComplete  => Aktif::class,
            $dataComplete && $mappingExists                    => BelumNilai::class,
            $dataComplete                                      => MappingSubCPMK::class,
            default                                            => Draft::class,
        };

        if ($mk->status instanceof $target) {
            return;
        }

        // Transisi step-by-step agar tidak melompati state yang tidak terdaftar
        $currentIndex = array_search(get_class($mk->status), self::STATE_ORDER);
        $targetIndex  = array_search($target, self::STATE_ORDER);

        if ($currentIndex === false || $targetIndex === false) {
            return;
        }

        try {
            while ($currentIndex !== $targetIndex) {
                $nextIndex = $currentIndex < $targetIndex
                    ? $currentIndex + 1
                    : $currentIndex - 1;

                $mk->status->transitionTo(self::STATE_ORDER[$nextIndex]);
                $mk->refresh();
                $currentIndex = $nextIndex;
            }
        } catch (TransitionNotAllowed | TransitionNotFound $e) {
            // Transisi tidak dapat dilanjutkan; berhenti di state saat ini
        }
    }
}
