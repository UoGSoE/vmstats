<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>VMStats</title>

        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        @livewireStyles
    </head>
    <body>
        <div class="section">
            <div class="container">
                <h1 class="title is-3">VM Stats</h1>
                <hr>
                @livewire('vm-list')
            </div>
        </div>
        @livewireScripts
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
