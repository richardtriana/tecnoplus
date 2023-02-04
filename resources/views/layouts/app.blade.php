<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<title>{{ config('app.name', 'Laravel') }}</title>

	<!-- Fonts -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">

	<!-- Styles -->
	<link href="{{ asset('css/fonts.css') }}" rel="stylesheet">
	<link href="{{ asset('css/app.css') }}" rel="stylesheet">


	<!-- Scripts -->
	<script src="{{ asset('js/shortcut.js')}} "></script>
	<script src="{{ asset('js/app.js') }}" defer></script>
	

</head>

<body>
	<div id="app">
		<div id="wrapper">
			<!-- Sidebar -->
			@component('components.navigation-bar')
			@endcomponent
			<main id="content-wrapper" class="d-flex flex-column">			
				<div class="justify-content-center">
					<router-view />
				</div>
				@component('components.footer')
				@endcomponent
			</main>
		</div>
	</div>

</body>

</html>