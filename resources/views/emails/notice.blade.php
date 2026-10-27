@php
    $C = [
        'red' => ['#B02A19', '#FBEDEA', '#C2311F'],
        'green' => ['#16653F', '#E6F4EC', '#15161C'],
        'blue' => ['#1A3F7F', '#EAF0FA', '#15161C'],
        'purple' => ['#5B3A9E', '#EDE7F6', '#15161C'],
        'orange' => ['#B4410F', '#FDECE4', '#C2311F'],
        'gray' => ['#4E515C', '#F0EEE9', '#15161C'],
    ];
    [$tagFg, $tagBg, $btnBg] = $C[$opt['c'] ?? 'gray'] ?? $C['gray'];
    $rows = $opt['rows'] ?? [];
    $photos = array_slice($opt['photos'] ?? [], 0, 4);
    $nama = $opt['nama'] ?? null;
    $email = $opt['email'] ?? '';
@endphp
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $judul }}</title></head>
<body style="margin:0;background:#F4F3EF;font-family:'Plus Jakarta Sans',Arial,Helvetica,sans-serif;color:#15161C">
@if (! empty($opt['pre']))<div style="display:none;max-height:0;overflow:hidden;opacity:0">{{ $opt['pre'] }}</div>@endif
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F4F3EF;padding:28px 12px">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#FFFFFF;border-radius:18px;overflow:hidden">
<tr><td style="background:#15161C;padding:18px 28px;color:#FFFFFF;font-weight:800;font-size:17px">
<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#E2553F;vertical-align:middle"></span><span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:#5B8BD9;margin-left:-5px;vertical-align:middle;opacity:.95"></span>
&nbsp;{{ config('jastip.nama') }}
</td></tr>
<tr><td style="padding:28px 28px 8px">
@if (! empty($opt['tag']))<span style="display:inline-block;font-size:11.5px;font-weight:800;letter-spacing:.06em;color:{{ $tagFg }};background:{{ $tagBg }};padding:5px 10px;border-radius:999px">{{ $opt['tag'] }}</span>@endif
<h1 style="margin:14px 0 12px;font-size:24px;line-height:1.3;font-weight:800">{{ $judul }}</h1>
<p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:#2C2E36">{{ $nama ? 'Hai '.$nama.',' : 'Hai,' }}</p>
@foreach ($baris as $b)
<p style="margin:0 0 8px;font-size:15px;line-height:1.6;color:#2C2E36">{{ $b }}</p>
@endforeach
</td></tr>
@if ($kode)
<tr><td style="padding:8px 28px 8px"><div style="font-size:34px;font-weight:800;letter-spacing:.3em;background:#F4F3EF;border-radius:14px;padding:16px;text-align:center">{{ $kode }}</div></td></tr>
@endif
@if ($rows)
<tr><td style="padding:10px 28px 4px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F7F6F2;border-radius:14px;padding:6px 16px">
@foreach ($rows as $r)
<tr>
<td style="padding:10px 0;font-size:14.5px;color:#4E515C;{{ $loop->last ? '' : 'border-bottom:1px solid #E6E4DF' }}">{{ $r[0] }}</td>
<td align="right" style="padding:10px 0;font-size:{{ ! empty($r[2]) ? '17px' : '14.5px' }};font-weight:{{ ! empty($r[2]) ? 800 : 600 }};{{ $loop->last ? '' : 'border-bottom:1px solid #E6E4DF' }}">{{ $r[1] }}</td>
</tr>
@endforeach
</table>
</td></tr>
@endif
@if ($photos)
<tr><td style="padding:10px 28px 4px">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
@foreach ($photos as $ph)<td width="25%" style="padding:0 4px 0 0"><img src="{{ $ph }}" alt="Foto barang {{ $loop->iteration }}" width="118" style="width:100%;height:auto;border-radius:10px;display:block"></td>@endforeach
</tr></table>
</td></tr>
@endif
@if ($tombol && $url)
<tr><td style="padding:18px 28px 4px"><a href="{{ $url }}" style="display:inline-block;background:{{ $btnBg }};color:#FFFFFF;text-decoration:none;font-weight:700;font-size:15px;padding:15px 24px;border-radius:12px">{{ $tombol }}</a></td></tr>
@endif
@if (! empty($opt['note']))
<tr><td style="padding:12px 28px 0;font-size:13px;line-height:1.55;color:#4E515C">{{ $opt['note'] }}</td></tr>
@endif
<tr><td style="padding:24px 28px 26px">
<div style="border-top:1px solid #E6E4DF;padding-top:16px;font-size:12.5px;line-height:1.6;color:#4E515C">
Email ini dikirim ke {{ $email }} karena kamu punya akun di {{ config('jastip.nama') }}.<br>
Butuh bantuan? Balas email ini atau WhatsApp {{ config('jastip.whatsapp') }}.
@if (! empty($opt['marketing']) && ! empty($opt['user_id']))<br><a href="{{ \Illuminate\Support\Facades\URL::signedRoute('unsubscribe', ['user' => $opt['user_id']]) }}" style="color:#4E515C">Berhenti menerima info war &amp; rilisan baru</a>@endif
</div>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
