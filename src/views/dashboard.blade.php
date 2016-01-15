@extends('laraPages::admin')

@section('content')
<article>
<h2>Hi {{ \Auth::user()->name }},</h2>
Good to see you again.
</article>
@endsection
