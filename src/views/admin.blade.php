<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta name="robots" content="NOINDEX, NOFOLLOW">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable = no">
	<title>{{ str_replace('www.','',$_SERVER['HTTP_HOST']) }} - Admin {{ \Request::segment(2) }}</title>
	<link href="/vendor/larapages/css/admin.css" rel="stylesheet">
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
    <script src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    @yield('scripts')
</body>
</html>
