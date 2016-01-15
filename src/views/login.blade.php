@extends('laraPages::admin')

@section('content')
{!! Form::open(array('url' => '/login','class'=>'login')) !!}
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

{!! Form::email   ('email',  null, ['placeholder'=>'E-mail', 'autofocus', 'class'=>$errors->has('email')?'error':'']) !!}
{!! Form::password('password',  null, ['placeholder'=>'Password', 'class'=>$errors->has('password')?'error':'']) !!}
<label for="remember_me">Remember me</label><input type="checkbox" name="remember" id="remember_me">

{!! Form::submit('Login') !!}

{!! Form::close() !!}
@endsection
