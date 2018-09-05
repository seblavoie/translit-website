<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Translit | Live music vizualizations from your files or microphone</title>
        <script type="text/javascript" src="js/dist/lodash.js"></script>
        <script type="text/javascript" src="js/dist/butterchurn.min.js"></script>
        <script type="text/javascript" src="js/dist/butterchurnPresets.min.js"></script>
        <script type="text/javascript" src="js/dist/butterchurnPresetsExtra.min.js"></script>
        <script defer src="js/dist/fontawesome-v520.js"></script>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-11152642-12"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments);}
          gtag('js', new Date());

          gtag('config', 'UA-11152642-12');
        </script>


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
