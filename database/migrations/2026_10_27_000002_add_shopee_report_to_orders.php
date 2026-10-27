<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Penitip lapor sudah checkout di Shopee (nomor pesanan Shopee) untuk dicek admin. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shopee_order_no', 60)->nullable()->after('shopee_sent_at');
            $table->timestamp('shopee_reported_at')->nullable()->after('shopee_order_no');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shopee_order_no', 'shopee_reported_at']);
        });
    }
};
