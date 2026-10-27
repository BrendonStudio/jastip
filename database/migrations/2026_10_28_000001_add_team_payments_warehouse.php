<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tim & akses (peran staf), verifikasi pembayaran + refund,
 * portal warehouse, daftar tunggu war, dan pelaku di catatan aktivitas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 10)->nullable()->after('is_admin');        // owner | admin | keu | wh
            $table->string('wh_country', 2)->nullable()->after('role');       // negara gudang untuk staf warehouse
            $table->string('invite_token', 80)->nullable()->after('wh_country');
            $table->timestamp('invited_at')->nullable()->after('invite_token');
            $table->timestamp('last_seen_at')->nullable()->after('invited_at');
            $table->boolean('notify_war')->default(true)->after('favorite_group');
        });
        DB::table('users')->where('is_admin', true)->update(['role' => 'owner']);

        Schema::table('orders', function (Blueprint $table) {
            // Pembayaran tahap 1
            $table->string('pay_method', 10)->nullable()->after('payment_proof');   // transfer | qris
            $table->string('payer_name', 120)->nullable()->after('pay_method');
            $table->string('payer_bank', 60)->nullable()->after('payer_name');
            $table->string('payment_reject_reason')->nullable()->after('paid_at');
            $table->timestamp('payment_rejected_at')->nullable()->after('payment_reject_reason');
            // Batal / tolak penawaran
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->string('cancel_reason')->nullable()->after('cancelled_at');
            // Refund (kelebihan bayar, stok habis, dll.)
            $table->unsignedBigInteger('refund_amount')->default(0)->after('cancel_reason');
            $table->string('refund_reason')->nullable()->after('refund_amount');
            $table->string('refund_proof')->nullable()->after('refund_reason');
            $table->timestamp('refund_requested_at')->nullable()->after('refund_proof');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
            // Warehouse
            $table->string('local_resi', 80)->nullable()->after('checked_out_at');
            $table->timestamp('wh_arrived_at')->nullable()->after('local_resi');
            $table->timestamp('wh_submitted_at')->nullable()->after('wh_arrived_at');
            $table->string('wh_condition', 10)->nullable()->after('wh_submitted_at');
            $table->text('wh_note')->nullable()->after('wh_condition');
        });

        Schema::table('war_entries', function (Blueprint $table) {
            $table->string('dp_method', 10)->nullable()->after('dp_proof');
            $table->timestamp('dp_verified_at')->nullable()->after('dp_method');
            $table->string('dp_reject_reason')->nullable()->after('dp_verified_at');
            $table->string('settle_method', 10)->nullable()->after('settle_proof');
            $table->string('payer_name', 120)->nullable()->after('settle_method');
            $table->string('payer_bank', 60)->nullable()->after('payer_name');
            $table->string('settle_reject_reason')->nullable()->after('payer_bank');
            $table->string('refund_proof')->nullable()->after('refund_amount');
        });
        // DP lama dianggap sudah dicek
        DB::table('war_entries')->whereNotNull('dp_proof')->update(['dp_verified_at' => DB::raw('created_at')]);

        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        Schema::create('war_waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_drop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();
            $table->unique(['war_drop_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('war_waitlist');
        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
        Schema::table('war_entries', function (Blueprint $table) {
            $table->dropColumn(['dp_method', 'dp_verified_at', 'dp_reject_reason', 'settle_method', 'payer_name', 'payer_bank', 'settle_reject_reason', 'refund_proof']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['pay_method', 'payer_name', 'payer_bank', 'payment_reject_reason', 'payment_rejected_at', 'cancelled_at', 'cancel_reason', 'refund_amount', 'refund_reason', 'refund_proof', 'refund_requested_at', 'refunded_at', 'local_resi', 'wh_arrived_at', 'wh_submitted_at', 'wh_condition', 'wh_note']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'wh_country', 'invite_token', 'invited_at', 'last_seen_at', 'notify_war']);
        });
    }
};
