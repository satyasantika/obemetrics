<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MK dengan status null atau 'aktif' (nilai lama sebelum state machine diperluas)
        // diubah ke 'draft' agar SyncMkState dapat menentukan state yang benar saat pertama kali dipanggil.
        DB::table('mks')
            ->whereNull('status')
            ->orWhere('status', 'aktif')
            ->update(['status' => 'draft']);
    }

    public function down(): void
    {
        DB::table('mks')
            ->where('status', 'draft')
            ->update(['status' => 'aktif']);
    }
};
