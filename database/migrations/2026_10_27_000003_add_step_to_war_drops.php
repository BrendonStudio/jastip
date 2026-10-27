<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Langkah drop war yang ditandai admin: 0 pendaftaran, 1 hari war, 2 input hasil, 3 pelunasan & refund. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('war_drops', function (Blueprint $table) {
            $table->unsignedTinyInteger('step')->default(0)->after('refund_mode');
        });
    }

    public function down(): void
    {
        Schema::table('war_drops', function (Blueprint $table) {
            $table->dropColumn('step');
        });
    }
};
