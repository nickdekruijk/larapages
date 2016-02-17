@extends('laraPages::admin')

@section('content')
<article>
<h2>Hi {{ \NickDeKruijk\LaraPages\LaraPagesAuth::user()->name }},</h2>
Good to see you again.
</article>
@endsection
