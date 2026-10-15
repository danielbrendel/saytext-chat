<!doctype html>
<html lang="{{ getLocale() }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<meta name="author" content="{{ env('APP_AUTHOR') }}">
		<meta name="description" content="{{ env('APP_SERVERNAME', '') }}">

		<meta property="og:title" content="{{ env('APP_SERVERNAME', '') }}">
		<meta property="og:description" content="{{ strip_tags(env('APP_SERVERTOPIC', '')) }}">
		<meta property="og:url" content="{{ url($_SERVER['REQUEST_URI']) }}">
		<meta property="og:image" content="{{ asset('img/preview.png') }}">
		
		<title>{{ ((env('APP_SERVERNAME')) ?? 'SayText Chat') }}</title>

		<link rel="stylesheet" type="text/css" href="{{ asset('css/app.css', true) }}"/>
        <link rel="icon" type="image/png" href="{{ asset('img/icons/logo.png') }}"/>

		@if (env('APP_ENABLEPWA', false))
		<link rel="manifest" href="{{ asset('manifest.json') }}"/>
		@endif
	</head>
	
	<body style="background-image: url('{{ asset('img/backgrounds/background0.png') }}');">
		{%content%}

		<script>
			@if (env('APP_ENABLEPWA'))
			window.onload = function() {
                if ('serviceWorker' in navigator) {
                    navigator.serviceWorker.register('./serviceworker.js', { scope: '/' })
                        .then(function(registration){
                            window.serviceWorkerEnabled = true;
                        }).catch(function(err){
                            window.serviceWorkerEnabled = false;
                            console.error(err);
                        });
                }
            };
			@endif

			document.addEventListener('DOMContentLoaded', function() {
				window.chatServerName = '{{ env('APP_SERVERNAME', '') }}';
				window.chatServerTopic = '{!! BBCode::transform(env('APP_SERVERTOPIC', '')) !!}';
				window.chatFetchDelay = {{ env('APP_DELAY_FETCH', 10000) }};
				window.chatOnlineDelay = {{ env('APP_DELAY_ONLINE', 10000) }};
				window.speakBetweenDelay = {{ env('APP_SPEAK_DELAY', 100) }};
				window.chatMaxBackgrounds = {{ $max_backgrounds ?? 1 }};

				@if (!empty($username))
				localStorage.setItem('cl_name', '{{ $username }}');
				@endif

				@if (!empty($userteam))
				localStorage.setItem('cl_team', '{{ $userteam }}');
				@endif

				if (window.innerWidth < 768) {
					window.toggleUserList();
				}
			});
		</script>
		<script src="{{ asset('js/app.js', true) }}"></script>
	</body>
</html>