<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <meta name="theme-color" content="#0f0f12">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <script>
      (function () {
        try {
          var pref = localStorage.getItem('theme') || 'dark';
          if (pref !== 'light' && pref !== 'dark' && pref !== 'system') pref = 'dark';
          var root = document.documentElement;
          root.setAttribute('data-theme', pref);
          root.classList.remove('dark', 'light', 'system');
          root.classList.add(pref);
          var resolved = pref;
          if (pref === 'system') {
            resolved = window.matchMedia('(prefers-color-scheme: light)').matches
              ? 'light'
              : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'dark');
          }
          root.classList.toggle('scheme-dark', resolved === 'dark');
          root.classList.toggle('scheme-light', resolved === 'light');
          var meta = document.querySelector('meta[name="theme-color"]');
          if (meta) meta.setAttribute('content', resolved === 'dark' ? '#0f0f12' : '#f4f4f6');
        } catch (e) {
          document.documentElement.setAttribute('data-theme', 'dark');
          document.documentElement.classList.add('dark', 'scheme-dark');
        }
      })();
    </script>

    <title>Title</title>

    @vite(['resources/css/theme.css', 'resources/css/output.css'])
  </head>
  <body class="antialiased">
    <div id="app"></div>

    @vite('resources/js/app.js')
  </body>
</html>
