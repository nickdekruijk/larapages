<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="robots" content="NOINDEX, NOFOLLOW">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable = no">
	<title>{{ str_replace('www.','',$_SERVER['HTTP_HOST']) }} - Admin @yield('title')</title>
	<link href="{{asset('/vendor/larapages/css/admin.css')}}" rel="stylesheet">
	@if (config('larapages.css'))
	@foreach (config('larapages.css') as $css)
	<link href="{{$css}}" rel="stylesheet">
	@endforeach
	@endif
</head>
<body>
@if (isset($admin))
    <header{!! isset($mini) && $mini?' class="mini"':'' !!}>
    	<nav>
    		<div id="menuicon"></div>
    		{!! $admin->nav() !!}
            <div class="clear"></div>
    	</nav>
    </header>
@endif
	@yield('content')
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    @yield('scripts')
</body>
</html>
