<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('id')->change();
            $table->string('username')->unique()->after('id');
            $table->string('prefix')->after('name')->nullable();
            $table->string('suffix')->after('prefix')->nullable();
            $table->string('gender')->after('suffix')->nullable(); //L,P
            $table->string('phone')->after('gender')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'prefix',
                'suffix',
                'gender',
                'phone',
            ]);
            $table->uuid('id')->change();
        });
    }
};
