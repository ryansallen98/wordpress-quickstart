<!DOCTYPE html>
<html @php(language_attributes())>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script>
    console.log('Header scripts pushed');
    (function () {
      try {
        var m = document.cookie.match(/(?:^|;\s*)theme=(light|dark)\b/);
        var t = m ? m[1] : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        var html = document.documentElement;
        // Avoid layout thrash: only touch if needed
        if (!html.classList.contains(t)) {
          html.classList.remove('light', 'dark');
          html.classList.add(t);
        }
      } catch (e) { }
    })();
  </script>
  @php(do_action('get_header'))
  @php(wp_head())

  @vite(['resources/css/app.css', 'resources/ts/app.ts'])
</head>

<body @php(body_class(['antialiased']))>
  @php(wp_body_open())
  <div id="app">
    <a class="sr-only focus:not-sr-only" href="#main">
      {{ __('Skip to content', 'wordpress-quickstart') }}
    </a>

    @include('sections.header')

    <main id="main" class="main container mx-auto p-4">
      @yield('content')
    </main>

    @hasSection('sidebar')
      <aside class="sidebar">
        @yield('sidebar')
      </aside>
    @endif

    @include('sections.footer')
  </div>

  <x-toast />

  @stack('modals')
  @stack('scripts')
  @php(do_action('get_footer'))
  @php(wp_footer())
</body>

</html>