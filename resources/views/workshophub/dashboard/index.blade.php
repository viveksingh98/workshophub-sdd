<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Studio dashboard | {{ $settings['studio_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themes/'.$settings['theme'].'.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dashboard.css') }}">
    @if (! empty($settings['favicon'])) <link rel="icon" href="{{ asset($settings['favicon']) }}"> @endif
    <script defer src="{{ asset('assets/js/workshophub.js') }}"></script>
    <script defer src="{{ asset('assets/js/dashboard.js') }}"></script>
  </head>
  <body data-dashboard>
    <header class="topbar">
      <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-mark">{{ $settings['logo_text'] }}</span>
        <span><strong>{{ $settings['studio_name'] }}</strong><small>Studio dashboard</small></span>
      </a>
      <form class="topbar-search" method="get" action="{{ route('dashboard') }}">
        <input type="hidden" name="section" value="search">
        <input name="q" value="{{ request('q') }}" placeholder="Search students, bookings, posts…" aria-label="Global search">
      </form>
      <div class="button-row">
        <button class="button" type="button" data-theme-toggle title="Light / dark">🌓</button>
        <a class="button" href="{{ route('dashboard', ['section' => 'help']) }}">Help</a>
        <a class="button" href="{{ route('home') }}" target="_blank" rel="noopener">View site ↗</a>
        <form method="post" action="{{ route('logout') }}">@csrf<button class="button" type="submit">Sign out</button></form>
      </div>
    </header>

    <main class="view-root">
      @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
      @if ($errors->any()) <div class="flash error">{{ $errors->first() }}</div> @endif

      <div class="admin-shell">
        <aside class="admin-nav">
          @foreach ([
            'home' => '🏠 Dashboard',
            'availability' => '🗓️ Availability',
            'bookings' => '📒 Bookings',
            'calendar' => '📆 Calendar',
            'students' => '🧑‍🎨 Students',
            'blog' => '✍️ Blog',
            'faqs' => '❓ FAQs',
            'web' => '🌐 Web management',
            'help' => '🛟 Help',
          ] as $key => $label)
            <a class="segment {{ $section === $key || ($section === 'student' && $key === 'students') || ($section === 'search' && $key === 'home') ? 'is-active' : '' }}"
               href="{{ route('dashboard', ['section' => $key]) }}">{{ $label }}</a>
          @endforeach
        </aside>

        <div class="admin-content">
          @include('workshophub.dashboard.sections.'.($section === 'student' ? 'student' : $section))
        </div>
      </div>
    </main>
  </body>
</html>
