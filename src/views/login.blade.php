@extends('laraPages::admin')

@section('content')
<form method="POST" accept-charset="UTF-8" class="login" novalidate>
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<h2>Login</h2>

@if (session('error'))
	<ul class="error">
		Sorry,
		<li>{{ ucfirst(session('error')) }}</li>
	</ul>
@endif

<input placeholder="Username" name="username" type="email"   {{ !session('username')?'autofocus="autofocus"':'' }} value="{{ session('username') }}">
<input placeholder="Password" name="password" type="password" {{ session('username')?'autofocus="autofocus"':'' }}>

<input type="submit" value="Login">
</form>
@endsection
