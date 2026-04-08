<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/svg+xml">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Title</title>

    @vite('resources/css/output.css')
  </head>
  <body class="antialiased bg-gray-100 text-gray-900">
    <div id="app"></div>

    @vite('resources/js/app.js')
  </body>
</html>