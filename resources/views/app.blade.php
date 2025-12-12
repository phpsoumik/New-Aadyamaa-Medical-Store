<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}"/>
        <title>{{env('APP_NAME')}}</title>
    </head>
    <body class="antialiased">
        <div id="app"></div>  
         <script>
            var laravel = @json(['baseURL' => url('/')]);
         </script>
        <script src="{{ asset('js/main.js') }}"></script>
    </body>
</html>
