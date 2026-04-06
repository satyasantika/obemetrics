<?php

namespace App\Actions;

use App\Models\Prodi;
use App\States\Prodi\Aktif;
use App\States\Prodi\Draft;
use App\States\Prodi\NonAktif;

class SyncProdiState
{
    public static function sync(Prodi $prodi): void
    {
        // Refresh agar status terbaca dari DB terkini
        $prodi->refresh();

        // State NonAktif hanya diubah secara manual — jangan disentuh
        if ($prodi->getRawOriginal('status') === NonAktif::$name) {
            return;
        }

        $hasUsers = $prodi->joinProdiUsers()->exists();
        $targetName = $hasUsers ? Aktif::$name : Draft::$name;

        if ($prodi->getRawOriginal('status') !== $targetName) {
            $prodi->forceFill(['status' => $targetName])->save();
        }
    }
}
