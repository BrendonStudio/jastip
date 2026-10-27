@if (session('flash') || session('error') || $errors->any())
<div class="flash" role="status" style="{{ $style ?? '' }}">
@if (session('flash'))<div>{{ session('flash') }}</div>@endif
@if (session('error'))<div class="err">{{ session('error') }}</div>@endif
@if ($errors->any())<div class="err">{{ $errors->first() }}</div>@endif
</div>
@endif
