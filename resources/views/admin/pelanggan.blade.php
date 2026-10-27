@extends('layouts.admin')
@section('title', 'Admin · Pelanggan')

@section('main')
<div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; flex-wrap: wrap">
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Pelanggan</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Semua akun penitip, riwayat, dan catatan.</p>
</div>
<form method="GET" action="{{ route('admin.customers') }}" style="display: flex; min-width: 260px; flex: 0 1 360px">
<label style="display: flex; flex: 1"><span class="sr-only">Cari pelanggan</span><input type="search" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau username Shopee" style="flex: 1; height: 46px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 14.5px; color: #15161C; background: #FFFFFF; min-width: 0"></label>
</form>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-start">
<section aria-labelledby="list-title" style="flex: 3 1 460px; min-width: 0; background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 10px">
<h2 id="list-title" style="margin: 0; font-size: 16px; font-weight: 800">Daftar pelanggan ({{ $users->total() }})</h2>
<div style="overflow-x: auto">
<table style="width: 100%; min-width: 560px; border-collapse: collapse; font-size: 14px">
<thead><tr style="text-align: left; font-size: 12px; color: #4E515C">
<th scope="col" style="padding: 8px 8px 8px 0; font-weight: 700; border-bottom: 1px solid #E6E4DF">Nama</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Shopee</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Pesanan</th>
<th scope="col" style="padding: 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF">War</th>
<th scope="col" style="padding: 8px 0 8px 8px; font-weight: 700; border-bottom: 1px solid #E6E4DF">Status</th>
</tr></thead>
<tbody>
@forelse ($users as $x)
@php $on = $detail && $detail['u']->id === $x->id; @endphp
@php $st = $x->is_flagged ? pill('red') + ['l' => 'Bermasalah'] : pill('green') + ['l' => 'Aktif']; @endphp
<tr style="background: {{ $on ? '#EAF0FA' : 'transparent' }}">
<td style="padding: 0; border-bottom: 1px solid #F0EEE9"><a href="{{ route('admin.customers', array_filter(['id' => $x->id, 'q' => request('q'), 'page' => request('page')])) }}" aria-current="{{ $on ? 'true' : 'false' }}" style="display: flex; flex-direction: column; justify-content: center; min-height: 48px; padding: 4px 8px 4px 0; text-decoration: none; font-weight: 800">{{ $x->displayName() }}<span style="font-size: 12px; font-weight: 500; color: #4E515C">{{ $x->email }}</span></a></td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9; color: #4E515C">{{ $x->shopee_username ? '@'.$x->shopee_username : '—' }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9">{{ $x->orders_count }}</td>
<td style="padding: 10px 8px; border-bottom: 1px solid #F0EEE9">{{ $x->war_menang_count }}/{{ $x->war_entries_count }}</td>
<td style="padding: 10px 0 10px 8px; border-bottom: 1px solid #F0EEE9"><span style="font-size: 12px; font-weight: 700; color: {{ $st['fg'] }}; background: {{ $st['bg'] }}; padding: 4px 8px; border-radius: 999px">{{ $st['l'] }}</span></td>
</tr>
@empty
<tr><td colspan="5" style="padding: 16px 0; color: #4E515C">Belum ada pelanggan.</td></tr>
@endforelse
</tbody>
</table>
</div>
@if ($users->hasPages())
<div style="display: flex; justify-content: space-between; font-size: 14px; font-weight: 700">
@if ($users->previousPageUrl())<a href="{{ $users->previousPageUrl() }}">← Sebelumnya</a>@else<span></span>@endif
@if ($users->nextPageUrl())<a href="{{ $users->nextPageUrl() }}">Berikutnya →</a>@endif
</div>
@endif
</section>

@if ($detail)
@php $u = $detail['u']; @endphp
<section aria-labelledby="det-title" style="flex: 2 1 320px; background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 16px">
<div style="display: flex; gap: 12px; align-items: center">
<div aria-hidden="true" style="width: 48px; height: 48px; border-radius: 50%; background: #15161C; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; flex: none">{{ inisial($u->displayName()) }}</div>
<div style="display: flex; flex-direction: column; gap: 2px"><h2 id="det-title" style="margin: 0; font-size: 18px; font-weight: 800">{{ $u->displayName() }}</h2><span style="font-size: 13px; color: #4E515C">Bergabung {{ tgl($u->created_at) }}</span></div>
</div>
<dl style="margin: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; font-size: 13.5px">
@foreach ([
    ['Email', $u->email], ['Shopee', $u->shopee_username ? '@'.$u->shopee_username : '—'],
    ['Total belanja', rp($detail['belanja'])], ['Saldo refund', rp($detail['refund'])],
    ['Grup favorit', $u->favorite_group ?: '—'], ['WhatsApp', $u->whatsapp ?: '—'],
] as [$dt, $dd])
<div style="background: #F7F6F2; border-radius: 10px; padding: 10px; min-width: 0"><dt style="color: #4E515C">{{ $dt }}</dt><dd style="margin: 2px 0 0; font-weight: 700; overflow-wrap: anywhere">{{ $dd }}</dd></div>
@endforeach
<div style="grid-column: 1 / -1; background: #F7F6F2; border-radius: 10px; padding: 10px"><dt style="color: #4E515C">Rekening refund</dt><dd style="margin: 2px 0 0; font-weight: 700">{{ $u->refund_account ?: '—' }}</dd></div>
</dl>
<div style="display: flex; flex-direction: column; gap: 8px">
<h3 style="margin: 0; font-size: 14px; font-weight: 800">Pesanan terakhir</h3>
@forelse ($detail['orders'] as $o)
<a href="{{ route('admin.orders', ['id' => $o->id]) }}" style="display: flex; justify-content: space-between; gap: 8px; padding: 10px 12px; border: 1px solid #E6E4DF; border-radius: 10px; text-decoration: none; font-size: 13.5px"><span><strong>#{{ $o->code }}</strong> · {{ $o->type === 'war' ? 'War' : 'Titip' }} {{ $o->neg()['nama'] }}</span><span style="color: #4E515C">{{ $o->card()['status'] }}</span></a>
@empty
<p style="margin: 0; font-size: 13.5px; color: #4E515C">Belum ada pesanan.</p>
@endforelse
@foreach ($detail['entries'] as $e)
@if (! $e->order_id)
<a href="{{ route('admin.war', ['drop' => $e->war_drop_id]) }}" style="display: flex; justify-content: space-between; gap: 8px; padding: 10px 12px; border: 1px solid #E6E4DF; border-radius: 10px; text-decoration: none; font-size: 13.5px"><span><strong>War</strong> · {{ \Illuminate\Support\Str::limit($e->drop->name, 30) }}</span><span style="color: #4E515C">{{ $e->card()['status'] }}</span></a>
@endif
@endforeach
</div>
<form method="POST" action="{{ route('admin.customers.update', $u) }}" style="display: flex; flex-direction: column; gap: 12px">
@csrf
<input type="hidden" name="q" value="{{ request('q') }}">
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Catatan admin
<textarea name="admin_note" rows="2" placeholder="Mis. suka minta packing kayu" style="border: 1px solid #CFCDC6; border-radius: 10px; padding: 10px 12px; font-family: inherit; font-size: 14px; color: #15161C; resize: vertical">{{ $u->admin_note }}</textarea>
</label>
<label style="display: flex; gap: 12px; align-items: flex-start; padding: 12px; border-radius: 12px; background: {{ $u->is_flagged ? '#FBEDEA' : '#F7F6F2' }}; font-size: 13.5px; line-height: 1.45; cursor: pointer">
<input type="checkbox" name="is_flagged" value="1" @checked($u->is_flagged) style="width: 20px; height: 20px; margin: 0; flex: none">
<span><strong>Tandai bermasalah</strong><br>Mis. DP hangus berulang. Akun ini tidak bisa ikut war sampai tanda dicabut.</span>
</label>
<button type="submit" style="min-height: 44px; border: 1.5px solid #15161C; border-radius: 12px; background: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 14px; color: #15161C">Simpan catatan</button>
</form>
<div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px">
<a href="mailto:{{ $u->email }}" style="display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700; font-size: 14px">Kirim email</a>
@if ($u->whatsapp)
<a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $u->whatsapp)) }}" target="_blank" rel="noopener" style="display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700; font-size: 14px">Chat WA</a>
@else
<span style="display: flex; align-items: center; justify-content: center; min-height: 46px; border-radius: 12px; border: 1.5px solid #E6E4DF; color: #767884; font-weight: 700; font-size: 14px">WA belum diisi</span>
@endif
</div>
</section>
@endif
</div>
@endsection
