<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable = no">
	<meta name="description" content="{{ @$page->description }}">
	<title>
	    {{ str_replace('www.','',$_SERVER['HTTP_HOST']) }} - @yield('title')
    </title>
	<link href='//fonts.googleapis.com/css?family=Lato:300|News+Cycle:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="/css/all.css">
    @yield('html_head')
</head>
<body>
	<label class="menuicon" for="menuicon"><span></span><span></span><span></span></label><input type="checkbox" id="menuicon" role="button">
    <header class="smoothfont noselect">
    	<nav>
    		<a href="/" class="logo">Lara<span>Pages</span></a>
    		{!! $nav !!}
            <div class="clear"></div>
    	</nav>
        @yield('header')
    </header>
	<div class="main">
		@yield('content')
	    <footer>
	        @include('laraPages::main.footer')
	    </footer>
	</div>
    @yield('scripts')
</body>
</html>
