@extends('layouts.site')
@section('title', 'Status war · '.$d->name)
@section('body_class', 'bg-soft')

@section('content')
<div class="dsk" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #F4F3EF; width: 100%; min-height: 100vh">
@include('partials.header', ['dark' => true, 'active' => 'war'])
@include('partials.flash')
<div style="max-width: 640px; margin: 0 auto; padding: 64px 24px 80px">
@include('pages._war-entri-isi', ['m' => false])
</div>
</div>

<div class="mob">
<div class="mob-wrap" style="font-family: 'Plus Jakarta Sans', system-ui, sans-serif; color: #15161C; background: #FFFFFF">
@include('partials.mob-header', ['title' => 'Status war', 'back' => route('account')])
@include('partials.flash', ['style' => 'padding: 12px 16px 0'])
<div style="flex: 1; padding: 40px 20px 32px">
@include('pages._war-entri-isi', ['m' => true])
</div>
</div>
</div>
@endsection
