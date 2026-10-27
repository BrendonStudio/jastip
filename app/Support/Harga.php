<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Semua angka harga (kurs, tarif per kg, packing) dibaca dari tabel settings
 * yang diatur di Admin › Pengaturan harga.
 */
class Harga
{
    public const DEFAULTS = [
        'jp_kurs_modal' => 105,
        'jp_kurs_jual' => 110,
        'jp_tarif_modal' => 110000,
        'jp_tarif_jual' => 150000,
        'kr_kurs_modal' => 11.3,
        'kr_kurs_jual' => 12,
        'kr_tarif_modal' => 130000,
        'kr_tarif_jual' => 170000,
        'pack_standar_modal' => 8000,
        'pack_standar_jual' => 15000,
        'pack_bubble_modal' => 18000,
        'pack_bubble_jual' => 30000,
        'pack_kayu_modal' => 50000,
        'pack_kayu_jual' => 75000,
    ];

    public const PACK_LABEL = [
        'standar' => 'Standar',
        'bubble' => 'Bubble wrap',
        'kayu' => 'Peti kayu',
    ];

    public const PACK_DESC = [
        'standar' => 'Kardus + plastik. Cocok untuk merch kain (hoodie, slogan), majalah, dan barang yang tidak mudah rusak.',
        'bubble' => 'Bubble wrap berlapis + pelindung sudut. Paling aman untuk album supaya sudutnya tidak penyok.',
        'kayu' => 'Peti kayu atau kardus double. Untuk lightstick, banyak album sekaligus, dan barang mudah pecah.',
    ];

    protected static ?array $cache = null;

    public static function all(): array
    {
        if (static::$cache === null) {
            $saved = Setting::query()->pluck('value', 'key')->all();
            $vals = [];
            foreach (static::DEFAULTS as $k => $def) {
                $vals[$k] = isset($saved[$k]) && $saved[$k] !== '' ? (float) $saved[$k] : (float) $def;
            }
            static::$cache = $vals;
        }

        return static::$cache;
    }

    public static function forget(): void
    {
        static::$cache = null;
    }

    public static function get(string $key): float
    {
        return static::all()[$key] ?? 0.0;
    }

    /** ['km','kj','tm','tj'] untuk jp / kr */
    public static function negara(string $c): array
    {
        $c = $c === 'kr' ? 'kr' : 'jp';
        $a = static::all();

        return [
            'km' => $a[$c.'_kurs_modal'],
            'kj' => $a[$c.'_kurs_jual'],
            'tm' => (int) $a[$c.'_tarif_modal'],
            'tj' => (int) $a[$c.'_tarif_jual'],
        ];
    }

    /** Daftar packing: id => [label, modal, jual, desc] */
    public static function packs(): array
    {
        $a = static::all();
        $out = [];
        foreach (static::PACK_LABEL as $id => $label) {
            $out[$id] = [
                'id' => $id,
                'label' => $label,
                'modal' => (int) $a['pack_'.$id.'_modal'],
                'jual' => (int) $a['pack_'.$id.'_jual'],
                'desc' => static::PACK_DESC[$id],
            ];
        }

        return $out;
    }

    /** Data untuk kalkulator di browser. */
    public static function forJs(): array
    {
        return [
            'jp' => static::negara('jp'),
            'kr' => static::negara('kr'),
            'packs' => static::packs(),
        ];
    }
}
