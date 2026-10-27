<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarController;
use Illuminate\Support\Facades\Route;

/* ---------------- Halaman penitip ---------------- */

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/war', [WarController::class, 'index'])->name('war');
Route::view('/ketentuan', 'pages.ketentuan')->name('terms');
Route::get('/berhenti-langganan/{user}', [AccountController::class, 'unsubscribe'])->middleware('signed')->name('unsubscribe');

// Undangan tim (staf admin / warehouse)
Route::get('/undangan/{token}', [Admin\TeamController::class, 'showInvite'])->name('invite.show');
Route::post('/undangan/{token}', [Admin\TeamController::class, 'acceptInvite'])->middleware('throttle:10,1')->name('invite.accept');

// Masuk / daftar pakai kode email (tanpa kata sandi)
Route::get('/masuk', [AuthController::class, 'showEmail'])->name('login');
Route::post('/masuk', [AuthController::class, 'sendCode'])->middleware('throttle:10,1')->name('login.send');
Route::get('/masuk/kode', [AuthController::class, 'showCode'])->name('login.code');
Route::post('/masuk/kode', [AuthController::class, 'verify'])->middleware('throttle:10,1')->name('login.verify');
Route::post('/masuk/kirim-ulang', [AuthController::class, 'resend'])->middleware('throttle:3,1')->name('login.resend');

Route::middleware('auth')->group(function () {
    Route::get('/masuk/profil', [AuthController::class, 'showProfile'])->name('profile');
    Route::post('/masuk/profil', [AuthController::class, 'saveProfile'])->name('profile.save');
    Route::get('/masuk/selesai', [AuthController::class, 'done'])->name('login.done');
    Route::post('/keluar', [AuthController::class, 'logout'])->name('logout');

    Route::get('/akun', [AccountController::class, 'index'])->name('account');
    Route::get('/akun/ubah', [AuthController::class, 'showProfile'])->name('account.edit');
    Route::get('/akun/riwayat', [AccountController::class, 'history'])->name('history');
    Route::post('/akun/info-war', [AccountController::class, 'toggleWarInfo'])->name('account.warinfo');

    // Penawaran & pembayaran
    Route::get('/bayar/{order}', [PayController::class, 'show'])->name('pay');
    Route::post('/bayar/{order}/tolak', [PayController::class, 'reject'])->name('pay.reject');
    Route::post('/bayar/{order}/minta-ulang', [PayController::class, 'renew'])->name('pay.renew');
    Route::get('/bayar/war/{entry}', [PayController::class, 'showWar'])->name('pay.war');
    Route::post('/bayar/war/{entry}', [PayController::class, 'submitWar'])->name('pay.war.submit');
    Route::post('/war/{drop}/tunggu', [WarController::class, 'waitlist'])->name('war.waitlist');

    Route::middleware('profil')->group(function () {
        Route::get('/titip', [OrderController::class, 'create'])->name('order.create');
        Route::post('/titip', [OrderController::class, 'store'])->name('order.store');
        Route::get('/war/{drop}/daftar', [WarController::class, 'register'])->name('war.register');
        Route::post('/war/{drop}/daftar', [WarController::class, 'store'])->name('war.store');
    });

    Route::get('/lacak', [OrderController::class, 'track'])->name('track');
    Route::post('/pesanan/{order}/bayar', [OrderController::class, 'pay'])->name('order.pay');
    Route::post('/pesanan/{order}/lapor-shopee', [OrderController::class, 'reportShopee'])->name('order.shopee');

    Route::get('/war/entri/{entry}', [WarController::class, 'entry'])->name('war.entry');
    Route::post('/war/entri/{entry}/lunasi', [WarController::class, 'settle'])->name('war.settle');
});

/* ---------------- Portal warehouse (staf gudang) ---------------- */

Route::middleware('admin')->prefix('gudang')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('warehouse');
    Route::get('/{order}', [WarehouseController::class, 'show'])->name('warehouse.show');
    Route::post('/{order}/tiba', [WarehouseController::class, 'arrived'])->name('warehouse.arrived');
    Route::post('/{order}', [WarehouseController::class, 'submit'])->name('warehouse.submit');
});

/* ---------------- Panel admin ---------------- */

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/masuk', [Admin\AuthController::class, 'show'])->name('login');
    Route::post('/masuk', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.post');

    Route::middleware('admin')->group(function () {
        Route::post('/keluar', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        // Pesanan per tahap
        Route::get('/pesanan', [Admin\OrderController::class, 'index'])->name('orders');
        Route::post('/pesanan/{order}/penawaran', [Admin\OrderController::class, 'offer'])->name('orders.offer');
        Route::post('/pesanan/{order}/lunas', [Admin\OrderController::class, 'markPaid'])->name('orders.paid');
        Route::post('/pesanan/{order}/tolak', [Admin\OrderController::class, 'rejectPayment'])->name('orders.reject');
        Route::post('/pesanan/{order}/checkout', [Admin\OrderController::class, 'checkout'])->name('orders.checkout');
        Route::post('/pesanan/{order}/foto', [Admin\OrderController::class, 'photos'])->name('orders.photos');
        Route::post('/pesanan/{order}/jadwal', [Admin\OrderController::class, 'schedule'])->name('orders.schedule');
        Route::post('/pesanan/{order}/tiba', [Admin\OrderController::class, 'arrived'])->name('orders.arrived');
        Route::post('/pesanan/{order}/shopee', [Admin\OrderController::class, 'shopee'])->name('orders.shopee');
        Route::post('/pesanan/{order}/selesai', [Admin\OrderController::class, 'complete'])->name('orders.complete');
        Route::post('/pesanan/{order}/mundur', [Admin\OrderController::class, 'back'])->name('orders.back');
        Route::post('/pesanan/{order}/refund', [Admin\OrderController::class, 'refund'])->name('orders.refund');

        // Pembayaran & refund
        Route::get('/pembayaran', [Admin\PaymentController::class, 'index'])->name('payments');
        Route::post('/pembayaran/dp/{entry}', [Admin\PaymentController::class, 'verifyDp'])->name('payments.dp');
        Route::post('/pembayaran/dp/{entry}/tolak', [Admin\PaymentController::class, 'rejectDp'])->name('payments.dp.reject');
        Route::post('/pembayaran/pelunasan/{entry}/tolak', [Admin\PaymentController::class, 'rejectSettle'])->name('payments.settle.reject');
        Route::post('/pembayaran/refund/{kind}/{id}', [Admin\PaymentController::class, 'refundSent'])->whereIn('kind', ['war', 'order'])->whereNumber('id')->name('payments.refund');

        // Jasa war
        Route::get('/war', [Admin\WarController::class, 'index'])->name('war');
        Route::post('/war', [Admin\WarController::class, 'store'])->name('war.store');
        Route::post('/war/{drop}', [Admin\WarController::class, 'update'])->name('war.update');
        Route::post('/war/{drop}/notif', [Admin\WarController::class, 'notifyFans'])->name('war.notify');
        Route::post('/war/{drop}/kirim-hasil', [Admin\WarController::class, 'sendResults'])->name('war.results');
        Route::post('/war/{drop}/langkah', [Admin\WarController::class, 'advance'])->name('war.step');
        Route::post('/war/entri/{entry}/hasil', [Admin\WarController::class, 'setResult'])->name('war.result');
        Route::post('/war/entri/{entry}/lunas', [Admin\WarController::class, 'confirmSettle'])->name('war.settled');
        Route::post('/war/entri/{entry}/refund', [Admin\WarController::class, 'markRefunded'])->name('war.refunded');

        // Jadwal kirim
        Route::get('/jadwal', [Admin\BatchController::class, 'index'])->name('batches');
        Route::post('/jadwal', [Admin\BatchController::class, 'store'])->name('batches.store');
        Route::post('/jadwal/{batch}', [Admin\BatchController::class, 'update'])->name('batches.update');
        Route::post('/jadwal/{batch}/lanjut', [Admin\BatchController::class, 'next'])->name('batches.next');
        Route::post('/jadwal/{batch}/tahap', [Admin\BatchController::class, 'status'])->name('batches.status');
        Route::post('/jadwal/{batch}/paket/{order}', [Admin\BatchController::class, 'toggle'])->name('batches.toggle');

        // Harga, keuangan, pelanggan
        Route::get('/harga', [Admin\PricingController::class, 'index'])->name('pricing');
        Route::post('/harga', [Admin\PricingController::class, 'save'])->name('pricing.save');
        Route::post('/harga/qris', [Admin\PricingController::class, 'qris'])->name('pricing.qris');
        Route::get('/keuangan', [Admin\FinanceController::class, 'index'])->name('finance');
        Route::post('/keuangan/biaya', [Admin\FinanceController::class, 'saveCosts'])->name('finance.costs');
        Route::get('/keuangan/unduh', [Admin\FinanceController::class, 'download'])->name('finance.download');
        Route::get('/pelanggan', [Admin\CustomerController::class, 'index'])->name('customers');
        Route::post('/pelanggan/{user}', [Admin\CustomerController::class, 'update'])->name('customers.update');

        // Tim & akses
        Route::get('/tim', [Admin\TeamController::class, 'index'])->name('team');
        Route::post('/tim', [Admin\TeamController::class, 'invite'])->name('team.invite');
        Route::post('/tim/akses', [Admin\TeamController::class, 'perm'])->name('team.perm');
        Route::post('/tim/{user}', [Admin\TeamController::class, 'update'])->name('team.update');
    });
});
