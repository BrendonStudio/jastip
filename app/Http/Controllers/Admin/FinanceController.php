<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyCost;
use App\Models\Order;
use App\Support\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    protected function month(Request $request): string
    {
        $m = (string) $request->input('bulan', now()->format('Y-m'));

        return preg_match('/^\d{4}-\d{2}$/', $m) ? $m : now()->format('Y-m');
    }

    public function index(Request $request)
    {
        $month = $this->month($request);
        $first = Order::whereNotNull('paid_at')->min('paid_at');
        $start = $first ? Carbon::parse($first)->startOfMonth() : now()->startOfMonth();
        $months = [];
        for ($d = now()->startOfMonth(); $d->gte($start->copy()->subMonths(0)) && count($months) < 36; $d->subMonth()) {
            $months[] = $d->format('Y-m');
        }
        if (! in_array($month, $months, true)) {
            $months[] = $month;
        }

        return view('admin.keuangan', [
            'k' => Keuangan::bulan($month),
            'month' => $month,
            'months' => $months,
            'filter' => in_array($request->query('jenis'), ['titip', 'war'], true) ? $request->query('jenis') : 'semua',
        ]);
    }

    public function saveCosts(Request $request)
    {
        $month = $this->month($request);
        $data = $request->validate([
            'shopee_pct' => 'nullable|numeric|min:0|max:100',
            'sewa' => 'nullable|integer|min:0',
            'iklan' => 'nullable|integer|min:0',
            'gaji' => 'nullable|integer|min:0',
            'lain' => 'nullable|integer|min:0',
        ]);
        $data = array_map(fn ($v) => $v ?? 0, $data);
        MonthlyCost::updateOrCreate(['month' => $month], $data);

        return redirect()->route('admin.finance', ['bulan' => $month, 'jenis' => $request->input('jenis')])->with('flash', 'Biaya bulan ini disimpan.');
    }

    public function download(Request $request)
    {
        $month = $this->month($request);
        $k = Keuangan::bulan($month);
        $name = 'laporan-jastip-'.$month.'.csv';

        return response()->streamDownload(function () use ($k, $month) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Laporan keuangan', $month], ';');
            fputcsv($out, [], ';');
            fputcsv($out, ['Pesanan', 'Penitip', 'Jenis', 'Negara', 'Status', 'Untung kurs', 'Untung berat', 'Untung packing', 'Fee war', 'Potongan Shopee', 'Profit', 'Omzet'], ';');
            foreach ($k['rows'] as $r) {
                fputcsv($out, [$r['id'], $r['nama'], $r['jenis'], strtoupper($r['neg']), $r['status'], round($r['kurs']), round($r['b']), round($r['p']), round($r['fw']), round($r['pot']), round($r['profit']), round($r['omzet'])], ';');
            }
            fputcsv($out, [], ';');
            foreach ([
                'Omzet' => $k['omzet'], 'Profit kotor' => $k['kotor'], 'Potongan Shopee' => -$k['pot'],
                'Biaya operasional' => -$k['ops'], 'Profit bersih' => $k['bersih'],
            ] as $label => $v) {
                fputcsv($out, [$label, round($v)], ';');
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
