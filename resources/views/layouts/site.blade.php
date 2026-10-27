<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Beranda') · {{ nama_jastip() }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('css/app.css') }}?v=7">
<link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 34 22'%3E%3Ccircle cx='11' cy='11' r='10' fill='%23C2311F'/%3E%3Ccircle cx='23' cy='11' r='10' fill='%231D4E9E' fill-opacity='.92'/%3E%3C/svg%3E">
@stack('head')
</head>
<body class="@yield('body_class')">
@yield('content')
<div id="offline-bar" role="alert" hidden style="position: fixed; left: 16px; right: 16px; bottom: 16px; z-index: 90; max-width: 520px; margin: 0 auto; background: #15161C; color: #FFFFFF; border-radius: 16px; padding: 14px 16px; display: flex; gap: 12px; align-items: center; box-shadow: 0 12px 32px rgba(21,22,28,0.25); font-family: 'Plus Jakarta Sans', system-ui, sans-serif">
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF8A78" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="flex: none"><path d="M2 8.5a15 15 0 0 1 20 0"></path><path d="M5 12a10 10 0 0 1 14 0"></path><path d="M8.5 15.5a5 5 0 0 1 7 0"></path><path d="M12 19h.01"></path><path d="M3 3l18 18"></path></svg>
<span style="flex: 1; display: flex; flex-direction: column; gap: 2px; font-size: 13.5px; line-height: 1.4"><strong style="font-size: 14.5px">Koneksi terputus</strong>Data yang sudah kamu isi tetap tersimpan. Coba lagi saat internet kembali.</span>
<button type="button" onclick="location.reload()" style="min-height: 40px; padding: 0 14px; border: 0; border-radius: 10px; background: #FFFFFF; color: #15161C; font-family: inherit; font-weight: 700; font-size: 13.5px">Coba lagi</button>
</div>
<script src="{{ asset('js/app.js') }}?v=7"></script>
@stack('scripts')
</body>
</html>
