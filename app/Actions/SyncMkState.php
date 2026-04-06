<?php

namespace App\Actions;

use App\Models\Mk;
use App\States\Mk\Aktif;
use App\States\Mk\BelumNilai;
use App\States\Mk\Draft;
use App\States\Mk\MappingSubCPMK;
use App\States\Mk\NonAktif;
use Spatie\ModelStates\Exceptions\TransitionNotAllowed;

class SyncMkState
{
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

        if (!($mk->status instanceof $target)) {
            try {
                $mk->status->transitionTo($target);
            } catch (TransitionNotAllowed $e) {
                // Transisi tidak diizinkan dari state saat ini; abaikan
            }
        }
    }
}
