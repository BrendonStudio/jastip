@extends('layouts.site')
@section('title', 'Terima undangan tim')
@section('body_class', 'bg-soft')

@php $in = 'height: 50px; border: 1px solid #CFCDC6; border-radius: 12px; padding: 0 14px; font-family: inherit; font-size: 16px; color: #15161C'; @endphp

@section('content')
<div style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; box-sizing: border-box; background: #15161C">
<form method="POST" action="{{ route('invite.accept', $token) }}" style="width: 100%; max-width: 420px; background: #FFFFFF; border-radius: 24px; padding: 32px; box-sizing: border-box; display: flex; flex-direction: column; gap: 18px">
@csrf
<div style="display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 18px">@include('partials.logo') {{ nama_jastip() }}</div>
<div style="display: flex; flex-direction: column; gap: 6px">
<span style="align-self: flex-start; font-size: 12px; font-weight: 700; color: {{ \App\Support\Akses::ROLE_COLOR[$u->role]['fg'] }}; background: {{ \App\Support\Akses::ROLE_COLOR[$u->role]['bg'] }}; padding: 4px 10px; border-radius: 999px">{{ \App\Support\Akses::roleLabel($u) }}</span>
<h1 style="margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.02em">Gabung ke tim</h1>
<p style="margin: 0; font-size: 14.5px; line-height: 1.5; color: #4E515C">{{ $u->email }} · {{ \App\Support\Akses::ROLE_DESC[$u->role] ?? '' }}</p>
</div>
@include('partials.flash', ['style' => 'padding: 0; margin: 0'])
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Nama<input type="text" name="name" required maxlength="120" value="{{ old('name', $u->name) }}" autocomplete="name" style="{{ $in }}"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Buat kata sandi (min. 8 karakter)<input type="password" name="password" required minlength="8" autocomplete="new-password" style="{{ $in }}"></label>
<label style="display: flex; flex-direction: column; gap: 6px; font-size: 13px; font-weight: 600; color: #4E515C">Ulangi kata sandi<input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" style="{{ $in }}"></label>
<button type="submit" style="min-height: 52px; border: 0; border-radius: 12px; background: #15161C; color: #FFFFFF; font-family: inherit; font-weight: 700; font-size: 16px">Gabung &amp; masuk</button>
</form>
</div>
@endsection
