<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings['studio_name'] }} | WorkshopHub SDD Demo</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themes/'.$settings['theme'].'.css') }}">
    <script defer src="{{ asset('assets/js/workshophub.js') }}"></script>
  </head>
  <body>
    <header class="topbar">
      <a class="brand" href="{{ route('home') }}">
        <span class="brand-mark">{{ $settings['logo_text'] }}</span>
        <span><strong>{{ $settings['studio_name'] }}</strong><small>{{ $settings['tagline'] }}</small></span>
      </a>
      <nav class="nav-tabs" aria-label="Main demo views">
        @foreach (['public' => 'Public', 'booking' => 'Booking', 'equipment' => 'Equipment', 'admin' => 'Admin', 'setup' => 'Setup', 'sdd' => 'SDD Map'] as $key => $label)
          <a class="nav-tab {{ $view === $key ? 'is-active' : '' }}" href="{{ route('home', ['view' => $key]) }}">{{ $label }}</a>
        @endforeach
      </nav>
    </header>

    <main>
      <section class="hero" id="home">
        <img class="hero-image" src="{{ asset('assets/images/workshophub-studio.png') }}" alt="Community workshop studio">
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <p class="eyebrow">Laravel MySQL Spec Driven Demo</p>
          <h1>{{ $settings['studio_name'] }}</h1>
          <p>{{ $settings['hero_message'] }}</p>
          <div class="hero-actions">
            <a class="button primary" href="{{ route('home', ['view' => 'booking']) }}">Book a seat</a>
            <a class="button ghost" href="{{ route('home', ['view' => 'admin']) }}">Open owner view</a>
          </div>
        </div>
      </section>

      <section class="summary-band" aria-label="WorkshopHub status">
        <div class="metric-line"><strong>{{ $metrics['classes'] }}</strong><span>Classes</span></div>
        <div class="metric-line"><strong>{{ $metrics['openSeats'] }}</strong><span>Open seats</span></div>
        <div class="metric-line"><strong>{{ $metrics['bookings'] }}</strong><span>Bookings</span></div>
        <div class="metric-line"><strong>{{ $metrics['students'] }}</strong><span>Students</span></div>
      </section>

      @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
      @if (session('confirmation')) <div class="flash">{{ session('confirmation') }}</div> @endif
      @if ($errors->any()) <div class="flash error">{{ $errors->first() }}</div> @endif

      <section class="view-root">
        @if ($view === 'booking')
          @include('workshophub.partials.booking')
        @elseif ($view === 'equipment')
          @include('workshophub.partials.equipment')
        @elseif ($view === 'admin')
          @include('workshophub.partials.admin')
        @elseif ($view === 'setup')
          @include('workshophub.partials.setup')
        @elseif ($view === 'sdd')
          @include('workshophub.partials.sdd')
        @else
          @include('workshophub.partials.public')
        @endif
      </section>
    </main>

    <footer class="footer">
      <span>WorkshopHub SDD Demo</span>
      <span>Laravel, MySQL-ready, server-rendered Blade, native JavaScript, theme CSS folders.</span>
    </footer>
  </body>
</html>
