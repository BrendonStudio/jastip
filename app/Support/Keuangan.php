<?php

namespace App\Support;

use App\Models\MonthlyCost;
use App\Models\Order;
use App\Models\WarEntry;
use Illuminate\Support\Carbon;

/** Hitung profit per bulan (sama dengan rumus di halaman Keuangan pada design). */
class Keuangan
{
    public static function bulan(string $month): array
    {
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfDay();
        $end = $start->copy()->endOfMonth();
        $cost = MonthlyCost::forMonth($month);
        $pct = max(0, (float) $cost->shopee_pct);

        $rows = [];
        $orders = Order::with(['items', 'user'])->whereNotNull('paid_at')->whereBetween('paid_at', [$start, $end])->orderBy('paid_at')->get();
        foreach ($orders as $o) {
            $kurs = $o->profitKurs();
            $b = $o->profitBerat();
            $p = $o->profitPacking();
            $fw = (int) $o->war_fee;
            $tag2 = $o->weight ? $o->tahap2() : 0;
            $pot = $tag2 * $pct / 100;
            $done = $o->stage >= Order::DONE;
            $profit = $kurs + $b + $p + $fw - $pot;
            [$st, $c] = $o->financeStatus();
            $rows[] = [
                'id' => $o->code, 'order_id' => $o->id, 'jenis' => $o->type, 'neg' => $o->country,
                'status' => $st, 'color' => $c, 'nama' => $o->user?->displayName(),
                'kurs' => $kurs, 'b' => $b, 'p' => $p, 'fw' => $fw, 'pot' => $pot,
                'omzet' => $o->tahap1() + ($done ? $tag2 : 0),
                'profit' => $profit,
                'cair' => $done ? $profit : $kurs + $fw,
            ];
        }

        $losers = WarEntry::with(['drop', 'user'])->where('result', 'kalah')->whereBetween('created_at', [$start, $end])->get();
        foreach ($losers as $e) {
            $fw = $e->drop->refund_mode === 'potong' ? $e->fee * $e->qty : 0;
            $rows[] = [
                'id' => 'WAR-'.str_pad((string) $e->id, 4, '0', STR_PAD_LEFT), 'order_id' => null, 'jenis' => 'war', 'neg' => $e->drop->country,
                'status' => 'Kalah · DP refund', 'color' => 'gray', 'nama' => $e->user?->displayName(),
                'kurs' => 0, 'b' => 0, 'p' => 0, 'fw' => $fw, 'pot' => 0,
                'omzet' => $fw, 'profit' => $fw, 'cair' => $fw,
            ];
        }

        $sum = fn ($k, $arr = null) => array_sum(array_column($arr ?? $rows, $k));
        $kotor = $sum('kurs') + $sum('b') + $sum('p') + $sum('fw');
        $pot = $sum('pot');
        $ops = $cost->ops();
        $omzet = $sum('omzet');
        $bersih = $kotor - $pot - $ops;
        $jp = array_values(array_filter($rows, fn ($r) => $r['neg'] === 'jp'));
        $kr = array_values(array_filter($rows, fn ($r) => $r['neg'] === 'kr'));

        return [
            'rows' => $rows,
            'cost' => $cost,
            'pct' => $pct,
            'omzet' => $omzet,
            'kotor' => $kotor,
            'tKurs' => $sum('kurs'),
            'tB' => $sum('b'),
            'tP' => $sum('p'),
            'tF' => $sum('fw'),
            'pot' => $pot,
            'ops' => $ops,
            'bersih' => $bersih,
            'margin' => $omzet > 0 ? $bersih / $omzet * 100 : 0,
            'cair' => $sum('cair') - $ops,
            'belum' => $sum('profit') - $sum('cair'),
            'profitJp' => $sum('profit', $jp),
            'profitKr' => $sum('profit', $kr),
            'nJp' => count($jp),
            'nKr' => count($kr),
        ];
    }
}
