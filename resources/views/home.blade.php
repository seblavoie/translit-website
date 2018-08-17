<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Translit</title>
        <script type="text/javascript" src="https://unpkg.com/lodash"></script>
        <script type="text/javascript" src="https://unpkg.com/butterchurn"></script>
        <script type="text/javascript" src="https://unpkg.com/butterchurn-presets"></script>
        <script type="text/javascript" src="https://unpkg.com/butterchurn-presets/lib/butterchurnPresetsExtra.min.js"></script>
        @empty($prod)
        <script src="http://localhost:8098"></script>
        @endisset
        <link rel="stylesheet" href="css/app.css">
    </head>
    <body>
        <div id="app">
            <app></app>
        </div>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
