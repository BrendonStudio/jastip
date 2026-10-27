@extends('layouts.site')
@section('title', 'Masuk admin')
@section('body_class', 'bg-soft')

@section('content')
<div style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; background: #15161C">
<form method="POST" action="{{ route('admin.login.post') }}" style="width: 100%; max-width: 400px; background: #FFFFFF; border-radius: 24px; padding: 32px; box-sizing: border-box; display: flex; flex-direction: column; gap: 18px">
@csrf
<div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 18px">@include('partials.logo') {{ nama_jastip() }}</div>
<div style="display: flex; flex-direction: column; gap: 6px">
<h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.02em">Masuk tim</h1>
<p style="margin: 0; font-size: 14.5px; color: #4E515C">Untuk admin, keuangan, dan staf warehouse.</p>
</div>
@include('partials.flash', ['style' => 'padding: 0; margin: 0'])
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Email
<input type="email" name="email" required autocomplete="username" value="{{ old('email') }}" style="height: 50px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 16px; color: #15161C">
</label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Kata sandi
<input type="password" name="password" required autocomplete="current-password" style="height: 50px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 16px; color: #15161C">
</label>
<button type="submit" style="min-height: 52px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px">Masuk</button>
<a href="{{ route('home') }}" style="font-size: 14px; font-weight: 700; color: #4E515C; text-align: center">← Kembali ke website</a>
</form>
</div>
@endsection
