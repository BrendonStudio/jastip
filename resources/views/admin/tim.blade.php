@extends('layouts.admin')
@section('title', 'Admin · Tim & akses')

@php
    $in = 'height: 46px; border: 1px solid #CFCDC6; border-radius: 10px; padding: 0 12px; font-family: inherit; font-size: 15px; color: #15161C; background: #FFFFFF; min-width: 0; width: 100%; box-sizing: border-box';
    $lab = 'display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C';
    $roles = ['owner', 'admin', 'keu', 'wh'];
    $status = function ($m) {
        if (! $m->password && $m->invite_token) {
            return ['Undangan terkirim', '#7A4B00'];
        }
        if ($m->last_seen_at && $m->last_seen_at->gt(now()->subMinutes(5))) {
            return ['Aktif sekarang', '#16653F'];
        }

        return [$m->last_seen_at ? 'Aktif '.yg_lalu($m->last_seen_at) : 'Belum pernah masuk', '#4E515C'];
    };
@endphp

@section('main')
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 30px; font-weight: 800; letter-spacing: -0.02em">Tim &amp; akses</h1>
<p style="margin: 0; font-size: 15px; color: #4E515C">Undang admin dan staf warehouse, atur menu yang boleh mereka buka, dan lihat siapa mengubah apa.</p>
</div>

<div style="display: flex; flex-wrap: wrap; gap: 18px; align-items: flex-start">
<section aria-labelledby="ang" style="flex: 3 1 560px; min-width: 0; background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="padding: 16px 20px; border-bottom: 1px solid #F0EEE9"><h2 id="ang" style="margin: 0; font-size: 17px; font-weight: 800">Anggota tim ({{ $members->count() }})</h2></div>
@foreach ($members as $m)
@php [$stTxt, $stFg] = $status($m); $rc = \App\Support\Akses::ROLE_COLOR[$m->role]; $nm = $m->name ?: $m->email; $locked = $m->role === 'owner' || $m->id === auth()->id(); @endphp
<div class="tim-row" style="display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(140px, 0.9fr) minmax(120px, 0.8fr) auto; gap: 14px; align-items: center; padding: 14px 20px; border-bottom: 1px solid #F0EEE9">
<span style="display: flex; gap: 12px; align-items: center; min-width: 0"><span aria-hidden="true" style="width: 38px; height: 38px; border-radius: 50%; background: {{ avatar_bg($nm) }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 800; flex: none">{{ inisial($m->name ?: strtok($m->email, '@')) }}</span><span style="display: flex; flex-direction: column; gap: 1px; min-width: 0"><span style="font-size: 14.5px; font-weight: 700">{{ $m->name ?: 'Belum diisi' }}{{ $m->id === auth()->id() ? ' (kamu)' : '' }}</span><span style="font-size: 12.5px; color: #4E515C; overflow: hidden; text-overflow: ellipsis">{{ $m->email }}</span></span></span>
<span style="justify-self: start; font-size: 12px; font-weight: 700; color: {{ $rc['fg'] }}; background: {{ $rc['bg'] }}; padding: 4px 10px; border-radius: 999px">{{ \App\Support\Akses::roleLabel($m) }}</span>
<span style="font-size: 12.5px; color: {{ $stFg }}; font-weight: 600">{{ $stTxt }}</span>
@if ($locked)
<span style="min-height: 36px; display: inline-flex; align-items: center; padding: 0 12px; font-size: 12.5px; color: #767884">—</span>
@else
<details class="rej-pop" style="position: relative">
<summary aria-label="Kelola {{ $nm }}" style="min-height: 36px; padding: 0 12px; border: 1px solid #E6E4DF; border-radius: 10px; background: #FFFFFF; font-weight: 700; font-size: 13px; color: #15161C; display: inline-flex; align-items: center; cursor: pointer; list-style: none">Kelola</summary>
<div style="position: absolute; right: 0; top: 42px; z-index: 5; width: 260px; background: #FFFFFF; border: 1px solid #E6E4DF; border-radius: 12px; box-shadow: 0 12px 30px rgba(21,22,28,0.15); padding: 12px; display: flex; flex-direction: column; gap: 10px">
<form method="POST" action="{{ route('admin.team.update', $m) }}" style="margin: 0; display: flex; flex-direction: column; gap: 8px">@csrf
<label style="{{ $lab }}">Peran<select name="role" style="{{ $in }}; height: 40px; font-weight: 700">@foreach (['admin', 'keu', 'wh'] as $r)<option value="{{ $r }}" @selected($m->role === $r)>{{ \App\Support\Akses::ROLES[$r] }}</option>@endforeach</select></label>
<label style="{{ $lab }}">Gudang (khusus warehouse)<select name="wh_country" style="{{ $in }}; height: 40px"><option value="">Semua</option><option value="jp" @selected($m->wh_country === 'jp')>Jepang</option><option value="kr" @selected($m->wh_country === 'kr')>Korea</option></select></label>
<button type="submit" style="min-height: 40px; border: 0; border-radius: 10px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 13.5px">Simpan</button>
</form>
<form method="POST" action="{{ route('admin.team.update', $m) }}" style="margin: 0">@csrf<input type="hidden" name="action" value="cabut"><button type="submit" data-confirm="Cabut akses {{ $nm }}? Akun ini tidak bisa masuk panel lagi." data-confirm-ok="Ya, cabut akses" style="width: 100%; min-height: 40px; border: 0; background: transparent; font-family: inherit; font-weight: 700; font-size: 13.5px; color: #B02A19">Cabut akses</button></form>
</div>
</details>
@endif
</div>
@endforeach
</section>

<section aria-labelledby="und" style="flex: 2 1 320px; min-width: 0; background: #FFFFFF; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; gap: 14px">
<h2 id="und" style="margin: 0; font-size: 17px; font-weight: 800">Undang anggota</h2>
<form method="POST" action="{{ route('admin.team.invite') }}" style="margin: 0; display: flex; flex-direction: column; gap: 14px">
@csrf
<label style="{{ $lab }}">Email<input type="email" name="email" required value="{{ old('email') }}" placeholder="nama@email.com" style="{{ $in }}"></label>
<div role="radiogroup" aria-label="Peran" style="display: flex; flex-direction: column; gap: 6px">
<span style="font-size: 13px; font-weight: 600; color: #4E515C">Peran</span>
<div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px">
@foreach (['admin', 'keu', 'wh'] as $r)
<label class="opt opt-neutral" style="min-height: 42px; border-radius: 10px; font-size: 12.5px; text-align: center"><input type="radio" name="role" value="{{ $r }}" @checked($invite === $r) data-role-desc="{{ \App\Support\Akses::ROLE_DESC[$r] }}">{{ \App\Support\Akses::ROLES[$r] }}</label>
@endforeach
</div>
<span style="font-size: 12.5px; color: #4E515C" data-role-out>{{ \App\Support\Akses::ROLE_DESC[$invite] }}</span>
</div>
<label style="{{ $lab }}">Gudang (untuk peran Warehouse)<select name="wh_country" style="{{ $in }}"><option value="">Semua gudang</option><option value="jp">Jepang</option><option value="kr">Korea</option></select></label>
<button type="submit" style="min-height: 46px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 15px">Kirim undangan via email</button>
</form>
</section>
</div>

<section aria-labelledby="hak" style="background: #FFFFFF; border-radius: 16px; overflow: hidden">
<div style="padding: 16px 20px; border-bottom: 1px solid #F0EEE9; display: flex; justify-content: space-between; align-items: baseline; gap: 12px; flex-wrap: wrap"><h2 id="hak" style="margin: 0; font-size: 17px; font-weight: 800">Hak akses per peran</h2><span style="font-size: 13px; color: #4E515C">Klik kotak untuk mengubah. Pemilik selalu punya semua akses.</span></div>
<div style="overflow-x: auto"><div style="min-width: 760px">
<div style="background: #FAFAF8; border-bottom: 1px solid #E6E4DF; display: grid; grid-template-columns: minmax(200px, 1.5fr) repeat(4, minmax(120px, 1fr)); gap: 10px; padding: 12px 20px; font-size: 12px; font-weight: 700; color: #4E515C"><span>Menu</span>@foreach ($roles as $r)<span style="text-align: center">{{ \App\Support\Akses::ROLES[$r] }}</span>@endforeach</div>
@foreach (\App\Support\Akses::MENUS as $key => [$label])
<div style="display: grid; grid-template-columns: minmax(200px, 1.5fr) repeat(4, minmax(120px, 1fr)); gap: 10px; align-items: center; padding: 8px 20px; border-bottom: 1px solid #F0EEE9; font-size: 14px">
<span style="font-weight: 700">{{ $label }}</span>
@foreach ($roles as $r)
@php
    $owner = $r === 'owner';
    $on = $owner ? $key !== 'gudang' : in_array($key, $perm[$r] ?? [], true);
@endphp
<span style="display: flex; justify-content: center">
@if ($owner)
<span role="img" aria-label="{{ $label }} untuk Pemilik: {{ $on ? 'boleh' : 'tidak' }}" style="width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid {{ $on ? '#4E515C' : '#CFCDC6' }}; background: {{ $on ? '#4E515C' : '#FFFFFF' }}; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 15px; font-weight: 800; opacity: 0.7; box-sizing: border-box">{{ $on ? '✓' : '' }}</span>
@else
<form method="POST" action="{{ route('admin.team.perm') }}" style="margin: 0">@csrf<input type="hidden" name="role" value="{{ $r }}"><input type="hidden" name="menu" value="{{ $key }}">
<button type="submit" role="checkbox" aria-checked="{{ $on ? 'true' : 'false' }}" aria-label="{{ $label }} untuk {{ \App\Support\Akses::ROLES[$r] }}" style="width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid {{ $on ? '#1D4E9E' : '#CFCDC6' }}; background: {{ $on ? '#1D4E9E' : '#FFFFFF' }}; color: #FFFFFF; font-family: inherit; font-size: 15px; font-weight: 800; cursor: pointer">{{ $on ? '✓' : '' }}</button>
</form>
@endif
</span>
@endforeach
</div>
@endforeach
</div></div>
</section>

<section aria-labelledby="log" style="background: #FFFFFF; border-radius: 16px; padding: 18px 20px; display: flex; flex-direction: column; gap: 4px">
<h2 id="log" style="margin: 0 0 8px; font-size: 17px; font-weight: 800">Catatan aktivitas</h2>
<ul style="list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; font-size: 14px">
@forelse ($log as $a)
<li style="display: grid; grid-template-columns: 90px 1fr; gap: 14px; padding: 11px 0; {{ $loop->last ? '' : 'border-bottom: 1px solid #F0EEE9' }}"><span style="color: #4E515C">{{ lalu($a->created_at) }}</span><span>{!! $a->html() !!}</span></li>
@empty
<li style="padding: 11px 0; color: #4E515C">Belum ada aktivitas tim.</li>
@endforelse
</ul>
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-role-desc]').forEach(function (r) {
  r.addEventListener('change', function () { var o = document.querySelector('[data-role-out]'); if (o) o.textContent = r.getAttribute('data-role-desc'); });
});
</script>
@endpush
