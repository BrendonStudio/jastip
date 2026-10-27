<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Batch;
use App\Models\MonthlyCost;
use App\Models\Order;
use App\Models\User;
use App\Models\WarDrop;
use App\Models\WarEntry;
use App\Support\Harga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Data contoh supaya semua halaman langsung terisi.
 * Hapus dengan: php artisan migrate:fresh --seed  (tanpa DemoSeeder)
 */
class DemoSeeder extends Seeder
{
    protected array $imgs = [];

    public function run(): void
    {
        $this->call(DatabaseSeeder::class);
        if (User::where('email', 'like', '%@contoh.test')->exists()) {
            $this->command?->info('Data contoh sudah ada, dilewati.');

            return;
        }
        $this->makeImages();

        $u = [];
        foreach ([
            ['Nadia Putri', 'nadiaputri', 'LUMINA'],
            ['Rara Amelia', 'rara.amel', 'NOVA, LUMINA'],
            ['Dimas Pratama', 'dimasprt', 'NOVA'],
            ['Sasa Kirana', 'sasakirana', 'LUMINA'],
            ['Bella Anggraini', 'bellaang', 'STARFALL'],
            ['Tiara Lestari', 'tiaralst', 'NOVA'],
        ] as $i => [$name, $shopee, $grp]) {
            $u[$i] = User::create([
                'name' => $name,
                'email' => strtolower(strtok($name, ' ')).'@contoh.test',
                'shopee_username' => $shopee,
                'whatsapp' => '0812000000'.($i + 10),
                'favorite_group' => $grp,
                'refund_account' => 'BCA 00000000'.$i.' a.n. '.$name,
                'email_verified_at' => now(),
                'created_at' => now()->subDays(40 - $i * 3),
            ]);
        }
        $u[5]->update(['is_flagged' => true, 'admin_note' => 'DP hangus 2x berturut-turut.']);

        $jp = Harga::negara('jp');
        $kr = Harga::negara('kr');
        $packs = Harga::packs();

        // Batch kirim
        $bJpNext = Batch::create(['country' => 'jp', 'route' => 'udara', 'cutoff_date' => today()->addDays(4), 'ship_date' => today()->addDays(6), 'eta_date' => today()->addDays(13), 'capacity_kg' => 30, 'status' => 0]);
        $bKrNext = Batch::create(['country' => 'kr', 'route' => 'udara', 'cutoff_date' => today()->addDays(7), 'ship_date' => today()->addDays(9), 'capacity_kg' => 25, 'status' => 0]);
        $bJpSea = Batch::create(['country' => 'jp', 'route' => 'laut', 'cutoff_date' => today()->subDays(12), 'ship_date' => today()->subDays(10), 'eta_date' => today()->addDays(15), 'capacity_kg' => 80, 'resi' => 'SEA-JP-2210', 'status' => 1]);
        $bKrDone = Batch::create(['country' => 'kr', 'route' => 'udara', 'cutoff_date' => today()->subDays(16), 'ship_date' => today()->subDays(14), 'eta_date' => today()->subDays(4), 'capacity_kg' => 25, 'resi' => 'AIR-KR-1408', 'status' => 2]);

        $mk = function (User $user, string $c, int $stage, array $items, array $extra = [], int $daysAgo = 5) use ($jp, $kr, $packs) {
            $p = $c === 'kr' ? $kr : $jp;
            $o = Order::create(['user_id' => $user->id, 'type' => $extra['type'] ?? 'titip', 'country' => $c, 'stage' => $stage, 'created_at' => now()->subDays($daysAgo), 'updated_at' => now()->subDays(max(0, $daysAgo - 2))]);
            foreach ($items as $it) {
                $o->items()->create($it + ['qty' => 1]);
            }
            $data = [];
            if ($stage >= 1) {
                $price = collect($items)->sum(fn ($i) => ($i['web_price'] ?? 0) * ($i['qty'] ?? 1));
                $data += ['store_price' => $price, 'domestic_shipping' => $c === 'jp' ? 600 : 3000, 'kurs_modal' => $p['km'], 'kurs_jual' => $p['kj'], 'tarif_modal' => $p['tm'], 'tarif_jual' => $p['tj'], 'offered_at' => now()->subDays($daysAgo - 1), 'offer_expires_at' => now()->addHours(18)];
            }
            if ($stage >= 2) {
                $data += ['payment_proof' => $this->imgs['transfer'] ?? null, 'payment_submitted_at' => now()->subDays($daysAgo - 1), 'paid_at' => now()->subDays($daysAgo - 1)];
            }
            if ($stage >= 3) {
                $data += ['store_order_no' => strtoupper($c).'-'.random_int(100000, 999999), 'checkout_proof' => $this->imgs['checkout'] ?? null, 'checked_out_at' => now()->subDays(max(0, $daysAgo - 2))];
            }
            $o->update(array_merge($data, $extra));
            if (isset($extra['pack_type'])) {
                $pk = $packs[$extra['pack_type']];
                $o->update(['pack_modal' => $pk['modal'], 'pack_jual' => $pk['jual']]);
            }
            // Waktu terakhir berubah dibuat bervariasi supaya "… lalu" di admin terlihat wajar
            $o->timestamps = false;
            $o->updated_at = now()->subMinutes(12 + ($o->id * 157) % 2600);
            $o->saveQuietly();
            $o->timestamps = true;

            return $o;
        };
        $photos = array_values(array_filter([$this->imgs['foto1'] ?? null, $this->imgs['foto2'] ?? null, $this->imgs['timbang'] ?? null]));
        $wh = fn ($w, $pack, $pc) => ['photos' => $photos, 'weight' => $w, 'pack_type' => $pack, 'photocard_note' => $pc, 'condition_note' => 'Sudut aman', 'warehouse_approved_at' => now()->subDays(2)];

        $mk($u[0], 'jp', 0, [
            ['name' => "LUMINA 3rd mini album 'Bloom'", 'variant' => 'Ver. A', 'web_price' => 2750, 'url' => 'https://example.com/lumina-bloom', 'note' => 'Kalau ver. A habis, ganti ver. B'],
            ['name' => 'LUMINA official photobook', 'variant' => null, 'web_price' => 4400, 'url' => 'https://example.com/photobook'],
        ], [], 1);
        $mk($u[1], 'kr', 1, [['name' => "NOVA 1st album 'Moonlit'", 'variant' => 'Digipack', 'web_price' => 18500, 'url' => 'https://example.com/nova-moonlit']], [], 2);
        $mk($u[2], 'jp', 1, [['name' => 'STARFALL Japan single (limited)', 'variant' => 'Type B', 'web_price' => 1650, 'url' => 'https://example.com/starfall-jp']], ['payment_proof' => $this->imgs['transfer'] ?? null, 'payment_submitted_at' => now()->subHours(3)], 3);
        $mk($u[3], 'kr', 2, [['name' => 'NOVA official lightstick ver. 2', 'web_price' => 49000, 'url' => 'https://example.com/nova-ls']], [], 4);
        $mk($u[0], 'jp', 3, [['name' => "LUMINA season's greetings 2027", 'web_price' => 6600, 'url' => 'https://example.com/sg']], [], 6);
        $mk($u[4], 'jp', 4, [['name' => "LUMINA 3rd mini album 'Bloom'", 'variant' => 'Ver. B', 'web_price' => 2750, 'qty' => 2]], $wh(1.3, 'bubble', 'Photocard Yuna, POB toko'), 10);
        $mk($u[2], 'jp', 5, [['name' => 'STARFALL concert MD set', 'web_price' => 9800]], $wh(2.4, 'kayu', 'POB tidak ada') + ['batch_id' => $bJpNext->id, 'scheduled_at' => now()->subDay()], 12);
        $mk($u[1], 'kr', 6, [['name' => "NOVA 1st album 'Moonlit'", 'variant' => 'Photobook ver.', 'web_price' => 21000]], $wh(0.8, 'standar', 'Photocard Jiho') + ['batch_id' => $bKrDone->id, 'scheduled_at' => now()->subDays(15), 'arrived_at' => now()->subDays(3)], 20);
        $mk($u[3], 'jp', 7, [['name' => 'LUMINA tour photobook + acrylic stand', 'web_price' => 7700, 'qty' => 2]], $wh(3.2, 'kayu', 'Acrylic Mina') + ['batch_id' => $bJpSea->id, 'scheduled_at' => now()->subDays(18), 'arrived_at' => now()->subDays(2), 'shopee_link' => 'https://shopee.co.id/', 'shopee_sent_at' => now()->subDay(), 'shopee_order_no' => '2410269KQ7MD3P', 'shopee_reported_at' => now()->subHours(3)], 22);
        $mk($u[4], 'kr', 8, [['name' => 'STARFALL Korea album', 'variant' => 'Ver. C', 'web_price' => 15000]], $wh(1.1, 'standar', 'Photocard Rin') + ['batch_id' => $bKrDone->id, 'scheduled_at' => now()->subDays(15), 'arrived_at' => now()->subDays(4), 'shopee_link' => 'https://shopee.co.id/', 'shopee_sent_at' => now()->subDays(3), 'shopee_order_no' => '2410249HX2TB8A', 'shopee_reported_at' => now()->subDays(2), 'completed_at' => now()->subDays(1)], 25);
        $mk($u[0], 'jp', 8, [['name' => 'LUMINA Japan 1st single', 'variant' => 'Regular', 'web_price' => 1320, 'qty' => 3]], $wh(0.6, 'bubble', 'Photocard random') + ['arrived_at' => now()->subDays(5), 'shopee_link' => 'https://shopee.co.id/', 'completed_at' => now()->subDays(2)], 18);

        // Jasa war
        $relA = now()->addDays(5)->setTime(10, 0);
        $a = WarDrop::create(['name' => "LUMINA 'Bloom' limited box (POB toko)", 'group_name' => 'LUMINA', 'country' => 'jp', 'release_at' => $relA, 'store_name' => 'Toko resmi JP', 'price' => 5500, 'variants' => ['Ver. A', 'Ver. B', 'Ver. C'], 'max_per_person' => 2, 'fee' => 25000, 'quota' => 12, 'close_hours' => 6, 'dp_pct' => 30, 'settle_hours' => 24, 'refund_mode' => 'potong', 'image' => $this->imgs['war1'] ?? null, 'release_url' => 'https://example.com/release-bloom']);
        $b = WarDrop::create(['name' => "NOVA pop-up 'Moonlit' MD set", 'group_name' => 'NOVA', 'country' => 'kr', 'release_at' => now()->addHours(20)->startOfHour(), 'store_name' => 'Pop-up store Seoul', 'price' => 45000, 'variants' => ['Set lengkap', 'Hoodie saja', 'Keyring saja'], 'max_per_person' => 1, 'fee' => 30000, 'quota' => 6, 'close_hours' => 6, 'dp_pct' => 30, 'settle_hours' => 24, 'refund_mode' => 'potong', 'image' => $this->imgs['war2'] ?? null]);
        $c = WarDrop::create(['name' => 'STARFALL fanclub limited album', 'group_name' => 'STARFALL', 'country' => 'jp', 'release_at' => now()->subHours(6)->startOfHour(), 'store_name' => 'Fanclub shop', 'price' => 3800, 'variants' => ['FC ver.', 'Regular'], 'max_per_person' => 2, 'fee' => 25000, 'quota' => 6, 'close_hours' => 6, 'dp_pct' => 30, 'settle_hours' => 24, 'refund_mode' => 'potong', 'image' => $this->imgs['war3'] ?? null]);

        $entry = function (WarDrop $d, User $user, int $q, string $v, ?string $result = null, bool $verified = true) use ($jp, $kr) {
            $p = $d->country === 'kr' ? $kr : $jp;
            $qty = 1;
            $total = (int) round($d->price * $qty * $p['kj'] + $d->fee * $qty);
            $e = WarEntry::create(['war_drop_id' => $d->id, 'user_id' => $user->id, 'queue_no' => $q, 'variant_main' => $v, 'variant_backup' => null, 'qty' => $qty, 'price' => $d->price, 'kurs_modal' => $p['km'], 'kurs_jual' => $p['kj'], 'fee' => $d->fee, 'total' => $total, 'dp_amount' => (int) round($total * $d->dp_pct / 100), 'dp_proof' => $this->imgs['transfer'] ?? null, 'result' => $result]);
            if ($result === 'menang') {
                $e->update(['settle_deadline' => now()->addHours(18)]);
            }
            if ($result === 'kalah') {
                $e->update(['refund_amount' => max(0, $e->dp_amount - $d->fee * $qty)]);
            }
            $e->timestamps = false;
            $e->created_at = $e->updated_at = now()->subMinutes(30 + ($e->id * 211) % 2000);
            $e->dp_verified_at = $verified ? $e->created_at->copy()->addMinutes(20) : null;
            $e->saveQuietly();
            $e->timestamps = true;

            return $e;
        };
        $entry($a, $u[0], 1, 'Ver. A');
        $entry($a, $u[3], 2, 'Ver. B');
        $entry($b, $u[1], 1, 'Set lengkap', null, false);
        $entry($c, $u[0], 1, 'FC ver.', 'menang');
        $entry($c, $u[2], 2, 'FC ver.', 'menang');
        $entry($c, $u[4], 3, 'Regular', 'kalah');
        $entry($c, $u[3], 4, 'Regular');

        // ---- Tim: admin, keuangan, warehouse ----
        $staff = [];
        foreach ([
            ['admin2@jastip.test', 'Admin Dua', 'admin', null, true, 180],
            ['keuangan@jastip.test', 'Rina Keuangan', 'keu', null, true, 60 * 26],
            ['gudang.kr@jastip.test', 'Staf Gudang Korea', 'wh', 'kr', true, 55],
            ['gudang.jp@jastip.test', null, 'wh', 'jp', false, null],
        ] as [$email, $name, $role, $wc, $active, $seen]) {
            $staff[$role.$wc] = User::create([
                'email' => $email, 'name' => $name, 'is_admin' => true, 'role' => $role, 'wh_country' => $wc,
                'password' => $active ? 'staf12345' : null, 'email_verified_at' => $active ? now() : null,
                'invite_token' => $active ? null : \Illuminate\Support\Str::random(48), 'invited_at' => now()->subDay(),
                'last_seen_at' => $seen ? now()->subMinutes($seen) : null,
            ]);
        }

        // ---- Paket di gudang Korea (portal warehouse) ----
        $kr3a = $mk($u[2], 'kr', 3, [['name' => "NOVA season's greetings 2027", 'web_price' => 38000], ['name' => "NOVA 1st album 'Moonlit'", 'variant' => 'Jewel case', 'web_price' => 9900]], ['local_resi' => 'CJ-58812044', 'wh_arrived_at' => now()->subHours(5), 'photos' => [$this->imgs['foto1'] ?? null, $this->imgs['foto2'] ?? null, null, null]], 5);
        $kr3b = $mk($u[3], 'kr', 3, [['name' => 'LUMINA pop-up acrylic stand set', 'variant' => 'Set A', 'web_price' => 28000]], ['local_resi' => 'CJ-58812107'], 3);
        $kr3c = $mk($u[1], 'kr', 3, [['name' => 'NOVA official photobook', 'web_price' => 32000]], ['local_resi' => 'HJ-22019931', 'wh_arrived_at' => now()->subDay(), 'photos' => [$this->imgs['foto1'] ?? null, $this->imgs['foto2'] ?? null, $this->imgs['war2'] ?? null, $this->imgs['timbang'] ?? null], 'weight' => 1.4, 'photocard_note' => 'Photocard Jiho', 'wh_condition' => 'penyok', 'condition_note' => 'Sudut penyok', 'wh_note' => 'Sudut kanan bawah box sedikit penyok dari kurir.', 'wh_submitted_at' => now()->subHours(2)], 6);

        // ---- Pembayaran ditolak & refund ----
        $mk($u[5], 'jp', 1, [['name' => 'STARFALL photocard binder', 'web_price' => 2200]], ['payment_rejected_at' => now()->subHours(7), 'payment_reject_reason' => 'Nominal di bukti Rp 260.000, seharusnya Rp 308.000'], 2);
        Order::where('stage', Order::WAREHOUSE)->first()?->update(['refund_amount' => 20000, 'refund_reason' => 'Kelebihan bayar', 'refund_requested_at' => now()->subHours(9)]);
        Order::where('stage', Order::DONE)->where('country', 'kr')->first()?->update(['refund_amount' => 15000, 'refund_reason' => 'Harga di toko turun', 'refund_requested_at' => now()->subDays(3), 'refunded_at' => now()->subDays(2)]);

        MonthlyCost::updateOrCreate(['month' => now()->format('Y-m')], ['shopee_pct' => 4, 'sewa' => 300000, 'iklan' => 150000, 'gaji' => 0, 'lain' => 0]);

        foreach ([
            [180, '**Rara Amelia** membayar DP war **NOVA pop-up \'Moonlit\' MD set**'],
            [150, 'Warehouse mengirim **3 foto** untuk pesanan TP-0006'],
            [95, '**Sasa Kirana** checkout di Shopee untuk pesanan TP-0010'],
            [60, 'Batch **'.tgl($bKrDone->ship_date).'** ditandai tiba di Indonesia'],
            [20, '**Dimas Pratama** mengirim bukti transfer tahap 1 untuk TP-0003'],
            [5, '**Nadia Putri** mengirim request titip baru dari Jepang (TP-0001)'],
        ] as [$min, $msg]) {
            Activity::create(['message' => $msg, 'created_at' => now()->subMinutes($min), 'updated_at' => now()->subMinutes($min)]);
        }
        $owner = User::where('role', 'owner')->first();
        foreach ([
            [$owner, 15, 'menandai lunas transfer **TP-0004**'],
            [$staff['whkr'], 120, 'mengirim 4 foto untuk **'.$kr3c->code.'** (1,4 kg · Sudut penyok)'],
            [$staff['admin'], 200, 'memindahkan **TP-0007** ke batch Jepang '.tgl($bJpNext->ship_date)],
            [$owner, 300, 'mengubah kurs jual ¥1 menjadi '.rpk($jp['kj'])],
            [$owner, 60 * 24, 'mengundang **gudang.jp@jastip.test** sebagai Warehouse · Jepang'],
        ] as [$who, $min, $msg]) {
            Activity::create(['user_id' => $who?->id, 'message' => $msg, 'created_at' => now()->subMinutes($min), 'updated_at' => now()->subMinutes($min)]);
        }

        $this->command?->info('Data contoh dibuat. Penitip contoh: nadia@contoh.test (masuk pakai kode email). Staf contoh: admin2@jastip.test, keuangan@jastip.test, gudang.kr@jastip.test (sandi: staf12345).');
    }

    /** Gambar contoh sederhana (butuh ekstensi GD; kalau tidak ada, dilewati). */
    protected function makeImages(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            return;
        }
        $dir = public_path('uploads/contoh');
        File::ensureDirectoryExists($dir);
        $specs = [
            'transfer' => [[234, 240, 250], [29, 78, 158], 'BUKTI TRANSFER'],
            'checkout' => [[230, 244, 236], [22, 101, 63], 'BUKTI CHECKOUT'],
            'foto1' => [[251, 237, 234], [194, 49, 31], 'FOTO BARANG 1'],
            'foto2' => [[255, 240, 209], [122, 75, 0], 'FOTO BARANG 2'],
            'timbang' => [[236, 234, 228], [21, 22, 28], 'TIMBANGAN'],
            'war1' => [[251, 237, 234], [194, 49, 31], 'LIMITED BOX'],
            'war2' => [[234, 240, 250], [29, 78, 158], 'POP-UP MD'],
            'war3' => [[244, 243, 239], [21, 22, 28], 'FC ALBUM'],
        ];
        foreach ($specs as $key => [$bg, $fg, $text]) {
            $im = imagecreatetruecolor(640, 480);
            imagefill($im, 0, 0, imagecolorallocate($im, ...$bg));
            $col = imagecolorallocate($im, ...$fg);
            imagefilledellipse($im, 270, 210, 180, 180, $col);
            imagefilledellipse($im, 370, 210, 180, 180, imagecolorallocatealpha($im, 29, 78, 158, 40));
            $x = (int) (320 - strlen($text) * imagefontwidth(5) / 2);
            imagestring($im, 5, $x, 360, $text, $col);
            imagepng($im, $dir.'/'.$key.'.png');
            imagedestroy($im);
            $this->imgs[$key] = 'contoh/'.$key.'.png';
        }
    }
}
