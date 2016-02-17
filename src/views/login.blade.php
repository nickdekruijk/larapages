@extends('laraPages::admin')

@section('content')
<form method="POST" accept-charset="UTF-8" class="login">
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<h2>Login</h2>

@if ($errors->any())
	<ul class="error">
		Sorry,
		@foreach ($errors->all() as $error)
			<li>{{ ucfirst($error) }}</li>
		@endforeach
	</ul>
@endif

<input placeholder="Username" name="username" type="email"   {{ !session('username')?'autofocus="autofocus"':'' }} value="{{ session('username') }}">
<input placeholder="Password" name="password" type="password" {{ session('username')?'autofocus="autofocus"':'' }}>

<input type="submit" value="Login">
</form>
@endsection
