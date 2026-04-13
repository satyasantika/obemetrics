<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('join_cpl_mks') && Schema::hasColumn('join_cpl_mks', 'join_cpl_bk_id')) {
            Schema::table('join_cpl_mks', function (Blueprint $table) {
                try {
                    $table->dropForeign(['join_cpl_bk_id']);
                } catch (\Throwable $e) {
                    // Ignore if FK does not exist on this environment.
                }
            });

            DB::statement('ALTER TABLE join_cpl_mks CHANGE join_cpl_bk_id cpl_bk_id CHAR(36) NULL');

            Schema::table('join_cpl_mks', function (Blueprint $table) {
                $table->foreign('cpl_bk_id')->references('id')->on('cpl_bks')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('join_cpl_cpmks') && Schema::hasColumn('join_cpl_cpmks', 'join_cpl_bk_id')) {
            Schema::table('join_cpl_cpmks', function (Blueprint $table) {
                try {
                    $table->dropForeign(['join_cpl_bk_id']);
                } catch (\Throwable $e) {
                    // Ignore if FK does not exist on this environment.
                }
            });

            DB::statement('ALTER TABLE join_cpl_cpmks CHANGE join_cpl_bk_id cpl_bk_id CHAR(36) NULL');

            Schema::table('join_cpl_cpmks', function (Blueprint $table) {
                $table->foreign('cpl_bk_id')->references('id')->on('cpl_bks')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('join_cpl_mks') && Schema::hasColumn('join_cpl_mks', 'cpl_bk_id')) {
            Schema::table('join_cpl_mks', function (Blueprint $table) {
                try {
                    $table->dropForeign(['cpl_bk_id']);
                } catch (\Throwable $e) {
                    // Ignore if FK does not exist on this environment.
                }
            });

            DB::statement('ALTER TABLE join_cpl_mks CHANGE cpl_bk_id join_cpl_bk_id CHAR(36) NULL');

            Schema::table('join_cpl_mks', function (Blueprint $table) {
                $table->foreign('join_cpl_bk_id')->references('id')->on('cpl_bks')->onUpdate('cascade')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('join_cpl_cpmks') && Schema::hasColumn('join_cpl_cpmks', 'cpl_bk_id')) {
            Schema::table('join_cpl_cpmks', function (Blueprint $table) {
                try {
                    $table->dropForeign(['cpl_bk_id']);
                } catch (\Throwable $e) {
                    // Ignore if FK does not exist on this environment.
                }
            });

            DB::statement('ALTER TABLE join_cpl_cpmks CHANGE cpl_bk_id join_cpl_bk_id CHAR(36) NULL');

            Schema::table('join_cpl_cpmks', function (Blueprint $table) {
                $table->foreign('join_cpl_bk_id')->references('id')->on('cpl_bks')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }
};
