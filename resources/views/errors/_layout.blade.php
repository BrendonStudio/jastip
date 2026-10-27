<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $title }} · {{ config('jastip.nama') }}</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=7">
</head>
<body class="bg-soft">
<main style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; font-family: 'Plus Jakarta Sans', system-ui, sans-serif">
<section style="max-width: 460px; width: 100%; background: #FFFFFF; border-radius: 20px; padding: 36px 28px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; box-sizing: border-box">
<span style="font-size: {{ $big ?? false ? '56px' : '13px' }}; font-weight: 800; letter-spacing: {{ $big ?? false ? '-0.04em' : '0.06em' }}; color: #C2311F">{{ $code }}</span>
<h1 style="margin: 0; font-size: 24px; font-weight: 800">{{ $title }}</h1>
<p style="margin: 0; font-size: 15px; line-height: 1.6; color: #4E515C">{{ $text }}</p>
<div style="margin-top: 8px; display: flex; gap: 10px; flex-wrap: wrap; justify-content: center">
<a href="{{ url('/') }}" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 20px; border-radius: 12px; background: #15161C; color: #FFFFFF; text-decoration: none; font-weight: 700">Ke beranda</a>
<a href="{{ url('/lacak') }}" style="display: inline-flex; align-items: center; min-height: 48px; padding: 0 20px; border-radius: 12px; border: 1.5px solid #15161C; text-decoration: none; font-weight: 700">Lacak pesanan</a>
</div>
</section>
</main>
</body>
</html>
