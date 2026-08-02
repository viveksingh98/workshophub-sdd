<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup wizard | WorkshopHub</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themes/studio.css') }}">
    <script defer src="{{ asset('assets/js/workshophub.js') }}"></script>
  </head>
  <body class="auth-body">
    <main class="auth-shell wide">
      <section class="panel auth-panel">
        <span class="brand-mark">WH</span>
        <h1>WorkshopHub setup wizard</h1>
        <p class="help-text">From zip file to running studio — five steps and the site is live.</p>

        <div class="flash {{ $connectionOk ? '' : 'error' }}">
          Database connection: {{ $connectionOk ? 'OK — migrations are in place.' : 'not reachable — check your .env values.' }}
        </div>
        @if ($errors->any()) <div class="flash error">{{ $errors->first() }}</div> @endif

        <form class="form-grid" method="post" action="{{ route('setup.install') }}" data-wizard>
          @csrf

          <fieldset class="wizard-step" data-step="1">
            <legend>Step 1 · Owner account (private)</legend>
            <label>Your name <input name="owner_name" value="{{ old('owner_name') }}" required></label>
            <div class="form-row">
              <label>Email <input name="email" type="email" value="{{ old('email') }}" required></label>
              <label>Phone <input name="phone" value="{{ old('phone') }}" required></label>
            </div>
            <div class="form-row">
              <label>Password <input name="password" type="password" minlength="8" required></label>
              <label>Repeat password <input name="password_confirmation" type="password" minlength="8" required></label>
            </div>
            <div class="field-note">These three unlock the dashboard — there is no other account.</div>
          </fieldset>

          <fieldset class="wizard-step is-hidden" data-step="2">
            <legend>Step 2 · Public details</legend>
            <label>Studio name <input name="studio_name" value="{{ old('studio_name', 'WorkshopHub') }}" required></label>
            <label>Tagline <input name="tagline" value="{{ old('tagline', 'Community studio booking') }}" required></label>
            <label>Address <input name="address" value="{{ old('address') }}" required></label>
          </fieldset>

          <fieldset class="wizard-step is-hidden" data-step="3">
            <legend>Step 3 · Class types &amp; pricing</legend>
            <label>Class types (comma separated) <input name="class_types" value="{{ old('class_types', 'Ceramics, Painting, Woodcraft, Writing') }}" required></label>
            <div class="form-row">
              <label>In-studio price <input name="pricing_in_studio" value="{{ old('pricing_in_studio', '₹1200 / class') }}" required></label>
              <label>Online price <input name="pricing_online" value="{{ old('pricing_online', '₹800 / class') }}" required></label>
            </div>
          </fieldset>

          <fieldset class="wizard-step is-hidden" data-step="4">
            <legend>Step 4 · Pick a theme</legend>
            <div class="theme-row">
              @foreach ($themes as $key => $label)
                <label class="theme-swatch">
                  <input type="radio" name="theme" value="{{ $key }}" @checked(old('theme', 'studio') === $key)>
                  <span class="swatch swatch-{{ $key }}"></span> {{ $label }}
                </label>
              @endforeach
            </div>
            <div class="field-note">Studio · Garden · Chalk · Night · Paper — switchable any time from the dashboard.</div>
          </fieldset>

          <div class="button-row">
            <button class="button is-hidden" type="button" data-wizard-prev>← Back</button>
            <button class="button primary" type="button" data-wizard-next>Next →</button>
            <button class="button primary is-hidden" type="submit" data-wizard-finish>Finish — make the site live</button>
          </div>
        </form>
      </section>
    </main>
  </body>
</html>
