<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Write Beautiful Documentation Inside Laravel Projects | LaRecipe</title>
        <meta name="description" content="{{ config('larecipe.seo.description') }}">
        
        <link rel="stylesheet" href="{{ larecipe_assets('css/app.css') }}">

        <link rel="apple-touch-icon" href="{{ asset(config('larecipe.ui.fav')) }}">
        <link rel="shortcut icon" type="image/png" href="{{ asset(config('larecipe.ui.fav')) }}"/>
        <link rel="stylesheet" href="{{ larecipe_assets('css/font-awesome.css') }}">
        <link rel="stylesheet" href="{{ larecipe_assets('css/font-awesome-v4-shims.css') }}">
        <style>
            .readme > ul:first-of-type {
                position: relative !important;
                width: 100%;
                top: 0;
                right: 0;
                left: 0;
            }
            @media (max-width: 780px) {
                .documentation {
                    padding: 0 15px !important;
                }
            }
        </style>
        @include('larecipe::style')
    </head>
    <body>
        <div id="app">
            @yield('content')
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.33.1/dist/sweetalert2.all.min.js"></script>

        @if(Session::has('success'))
            <script>
                swal({
                    title: "{{ session('success') }}",
                    buttonsStyling: false,
                    confirmButtonClass: "button is-success",
                    type: "success"
                }).catch(swal.noop)
            </script>
        @endif

        <script src="{{ larecipe_assets('js/app.js') }}"></script>

        {{-- Google Analytics --}}
        @if(config('larecipe.settings.ga_id'))
            <script async src="https://www.googletagmanager.com/gtag/js?id=G-DK7DYJ8SSR"></script>
            <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-DK7DYJ8SSR');
            </script>
        @endif
        {{-- /Google Analytics --}}
    </body>
</html>
