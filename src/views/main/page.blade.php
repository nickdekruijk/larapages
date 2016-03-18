@extends('laraPages::main.main')

@section('title')
{{ $page->html_title }}
@endsection

@section('content')
@if ($page->picture)
<div class="picture" style="background-image:url('/media/{{ trim(explode(chr(10),$page->picture)[0]) }}')">
    @if ($page->caption)
    <div class="caption">{!! $page->caption !!}</div>
    @endif
</div>
@endif
<article>
    <h1>{{ $page->head }}</h1>

    {!! $page->body !!}
    
</article>
<div class="clear"></div>

@endsection
