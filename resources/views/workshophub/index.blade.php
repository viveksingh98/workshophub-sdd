<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings['studio_name'] }} | {{ $settings['tagline'] }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themes/'.$settings['theme'].'.css') }}">
    @if (! empty($settings['favicon'])) <link rel="icon" href="{{ asset($settings['favicon']) }}"> @endif
    <script defer src="{{ asset('assets/js/workshophub.js') }}"></script>
  </head>
  <body>
    @if (! empty($previewingTheme))
      <div class="flash" style="margin:0;border-radius:0;text-align:center;">Theme preview: <b>{{ ucfirst($previewingTheme) }}</b> — your real data, not yet activated.</div>
    @endif
    <header class="topbar">
      <a class="brand" href="{{ route('home') }}">
        @if (! empty($settings['logo_image']))
          <img class="brand-logo" src="{{ asset($settings['logo_image']) }}" alt="{{ $settings['studio_name'] }} logo">
        @else
          <span class="brand-mark">{{ $settings['logo_text'] }}</span>
        @endif
        <span><strong>{{ $settings['studio_name'] }}</strong><small>{{ $settings['tagline'] }}</small></span>
      </a>
      <nav class="nav-tabs" aria-label="Main site pages">
        @foreach (['public' => 'Home', 'booking' => 'Book a class', 'blog' => 'Blog', 'equipment' => 'Equipment'] as $key => $label)
          <a class="nav-tab {{ $view === $key ? 'is-active' : '' }}" href="{{ route('home', ['view' => $key]) }}">{{ $label }}</a>
        @endforeach
        <a class="nav-tab" href="{{ route('login') }}">Owner login</a>
      </nav>
    </header>

    <main>
      <section class="hero" id="home">
        <img class="hero-image" src="{{ asset($settings['hero_image'] ?? 'assets/images/workshophub-studio.png') }}" alt="Community workshop studio">
        <div class="hero-overlay"></div>
        <div class="hero-content">
          <p class="eyebrow">{{ $settings['tagline'] }}</p>
          <h1>{{ $settings['studio_name'] }}</h1>
          <p>{{ $settings['hero_message'] }}</p>
          <div class="hero-actions">
            <a class="button primary" href="{{ route('home', ['view' => 'booking']) }}">Book a class</a>
            @if (! empty($settings['whatsapp_number']))
              <a class="button ghost" href="https://wa.me/{{ preg_replace('/\D+/', '', $settings['whatsapp_number']) }}" target="_blank" rel="noopener">WhatsApp us</a>
            @endif
          </div>
        </div>
      </section>

      <section class="summary-band" aria-label="Studio at a glance">
        <div class="metric-line"><strong>{{ $metrics['classes'] }}</strong><span>Classes</span></div>
        <div class="metric-line"><strong>{{ $metrics['instructors'] }}</strong><span>Instructors</span></div>
        <div class="metric-line"><strong>{{ $metrics['posts'] }}</strong><span>Blog posts</span></div>
        <div class="metric-line"><strong>{{ $metrics['faqs'] }}</strong><span>FAQs</span></div>
      </section>

      @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
      @if (session('confirmation'))
        <div class="flash confirmation-card">
          ✅ {{ session('confirmation') }}
          @if (session('gcal_url'))
            <a class="button primary" href="{{ session('gcal_url') }}" target="_blank" rel="noopener">Add to Google Calendar</a>
          @endif
        </div>
      @endif
      @if ($errors->any()) <div class="flash error">{{ $errors->first() }}</div> @endif

      <section class="view-root">
        @if ($view === 'booking')
          @include('workshophub.partials.booking')
        @elseif ($view === 'blog')
          @include('workshophub.partials.blog')
        @elseif ($view === 'equipment')
          @include('workshophub.partials.equipment')
        @else
          @include('workshophub.partials.public')
        @endif
      </section>
    </main>

    <footer class="footer">
      <div>
        <b>{{ $settings['studio_name'] }}</b> · {{ $settings['address'] }}
        <div class="button-row spaced">
          @if (! empty($settings['whatsapp_number']))
            <a class="button" href="https://wa.me/{{ preg_replace('/\D+/', '', $settings['whatsapp_number']) }}" target="_blank" rel="noopener">💬 WhatsApp</a>
          @endif
          @if (! empty($settings['contact_phone']))
            <a class="button" href="tel:{{ preg_replace('/[^\d+]/', '', $settings['contact_phone']) }}">📞 Call</a>
          @endif
          <a class="button" href="mailto:{{ $settings['contact_email'] }}">✉️ Email</a>
        </div>
      </div>
      <div class="button-row">
        @foreach (['social_instagram' => 'Instagram', 'social_youtube' => 'YouTube', 'social_facebook' => 'Facebook'] as $key => $label)
          @if (! empty($settings[$key]))
            <a class="button" href="{{ $settings[$key] }}" target="_blank" rel="noopener">{{ $label }}</a>
          @endif
        @endforeach
      </div>
    </footer>
  </body>
</html>
