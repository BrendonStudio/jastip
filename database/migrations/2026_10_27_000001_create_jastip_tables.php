<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_codes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });

        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('country', 2)->default('jp');
            $table->string('route', 10)->default('udara');      // udara | laut
            $table->date('cutoff_date')->nullable();
            $table->date('ship_date')->nullable();
            $table->date('eta_date')->nullable();
            $table->decimal('capacity_kg', 8, 2)->default(30);
            $table->string('resi')->nullable();
            $table->unsignedTinyInteger('status')->default(0);   // 0 kumpul, 1 dikirim, 2 tiba, 3 upload shopee
            $table->timestamps();
        });

        Schema::create('war_drops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('group_name')->nullable();
            $table->string('release_url')->nullable();
            $table->string('country', 2)->default('jp');
            $table->dateTime('release_at');                     // disimpan dalam WIB
            $table->string('store_name')->nullable();
            $table->decimal('price', 14, 2)->default(0);        // harga per barang dalam ¥ / ₩
            $table->text('variants')->nullable();               // JSON array
            $table->unsignedInteger('max_per_person')->default(2);
            $table->unsignedInteger('fee')->default(25000);
            $table->unsignedInteger('quota')->default(10);
            $table->unsignedInteger('close_hours')->default(6);
            $table->unsignedTinyInteger('dp_pct')->default(30);
            $table->unsignedInteger('settle_hours')->default(24);
            $table->string('refund_mode', 10)->default('potong'); // potong | penuh
            $table->string('image')->nullable();
            $table->timestamp('results_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 10)->default('titip');       // titip | war
            $table->string('country', 2)->default('jp');
            $table->unsignedTinyInteger('stage')->default(0);   // 0..8 lihat App\Models\Order
            // Penawaran (dikunci saat dikirim)
            $table->decimal('store_price', 14, 2)->nullable();
            $table->decimal('domestic_shipping', 14, 2)->default(0);
            $table->decimal('kurs_modal', 12, 4)->nullable();
            $table->decimal('kurs_jual', 12, 4)->nullable();
            $table->unsignedInteger('tarif_modal')->nullable();
            $table->unsignedInteger('tarif_jual')->nullable();
            $table->unsignedInteger('war_fee')->default(0);
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('offer_expires_at')->nullable();
            // Pembayaran tahap 1
            $table->string('payment_proof')->nullable();
            $table->timestamp('payment_submitted_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            // Checkout di toko
            $table->string('store_order_no')->nullable();
            $table->string('checkout_proof')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            // Warehouse
            $table->text('photos')->nullable();                 // JSON array path foto
            $table->string('photocard_note')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('condition_note')->nullable();
            $table->string('pack_type', 10)->nullable();
            $table->unsignedInteger('pack_modal')->nullable();
            $table->unsignedInteger('pack_jual')->nullable();
            $table->timestamp('warehouse_approved_at')->nullable();
            // Pengiriman
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            // Tahap 2 di Shopee
            $table->string('shopee_link')->nullable();
            $table->timestamp('shopee_sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('url', 1000)->nullable();
            $table->string('name');
            $table->string('variant')->nullable();
            $table->decimal('web_price', 14, 2)->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->text('note')->nullable();
            $table->string('screenshot')->nullable();
            $table->timestamps();
        });

        Schema::create('war_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('war_drop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('queue_no');
            $table->string('variant_main');
            $table->string('variant_backup')->nullable();
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 14, 2);
            $table->decimal('kurs_modal', 12, 4);
            $table->decimal('kurs_jual', 12, 4);
            $table->unsignedInteger('fee');
            $table->unsignedBigInteger('total');
            $table->unsignedBigInteger('dp_amount');
            $table->string('dp_proof')->nullable();
            $table->string('result', 10)->nullable();           // null | menang | kalah
            $table->timestamp('settle_deadline')->nullable();
            $table->string('settle_proof')->nullable();
            $table->timestamp('settle_submitted_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->unsignedBigInteger('refund_amount')->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('message', 500);
            $table->timestamps();
        });

        Schema::create('monthly_costs', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7)->unique();              // YYYY-MM
            $table->decimal('shopee_pct', 5, 2)->default(0);
            $table->unsignedBigInteger('sewa')->default(0);
            $table->unsignedBigInteger('iklan')->default(0);
            $table->unsignedBigInteger('gaji')->default(0);
            $table->unsignedBigInteger('lain')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['monthly_costs', 'activities', 'war_entries', 'order_items', 'orders', 'war_drops', 'batches', 'settings', 'login_codes'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
