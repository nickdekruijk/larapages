@extends('laraPages::admin')

@section('content')
<form method="POST" action="/login" accept-charset="UTF-8" class="login">
<input name="_token" type="hidden" value="{{ csrf_token() }}">
<h2>Login</h2>

@if ($errors->any())
	<ul class="error">
		Sorry,
		@foreach ($errors->all() as $error)
			<li>{{ ucfirst($error) }}</li>
		@endforeach
	</ul>
@endif
@if (Session::has('messages') && count(session('messages'))>0)
	<ul class="success">
		@foreach (session('messages') as $message)
			<li>{{ ucfirst($message) }}</li>
		@endforeach
	</ul>
@endif
<?php
    dd(Session::flash())
?>
<input class="{{ $errors->has('email')?'error':'' }}"    placeholder="E-mail"   value="" name="email" type="email" autofocus="autofocus" >
<input class="{{ $errors->has('password')?'error':'' }}" placeholder="Password" value="" name="password" type="password">
<label for="remember_me">Remember me</label><input type="checkbox" name="remember" id="remember_me">
<input type="submit" value="Login">

</form>
@endsection
