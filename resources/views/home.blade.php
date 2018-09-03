<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Translit</title>
        <script type="text/javascript" src="js/dist/lodash.js"></script>
        <script type="text/javascript" src="js/dist/butterchurn.min.js"></script>
        <script type="text/javascript" src="js/dist/butterchurnPresets.min.js"></script>
        <script type="text/javascript" src="js/dist/butterchurnPresetsExtra.min.js"></script>
        {{-- <script defer src="https://use.fontawesome.com/releases/v5.2.0/js/all.js" integrity="sha384-4oV5EgaV02iISL2ban6c/RmotsABqE4yZxZLcYMAdG7FAPsyHYAPpywE9PJo+Khy" crossorigin="anonymous"></script> --}}
        <script defer src="js/dist/fontawesome-v520.js"></script>


        @empty($prod)
        <script src="http://localhost:8098"></script>
        @endempty
        <link rel="stylesheet" href="css/app.css">
    </head>
    <body>
        <div id="app">
            <app></app>
        </div>
        <script src="js/app.js"></script>
    </body>
</html>
