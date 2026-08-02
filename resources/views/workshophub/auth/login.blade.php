<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Studio access | {{ $settings['studio_name'] }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/themes/'.$settings['theme'].'.css') }}">
  </head>
  <body class="auth-body">
    <main class="auth-shell">
      <section class="panel auth-panel">
        <span class="brand-mark">{{ $settings['logo_text'] }}</span>
        <h1>Studio access</h1>
        <p class="help-text">Three fields, one door — email, phone, and password must all match.</p>

        @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div class="flash error">{{ $errors->first() }}</div> @endif

        <form class="form-grid" method="post" action="{{ route('login.attempt') }}">
          @csrf
          <label>Email <input name="email" type="email" value="{{ old('email') }}" required autofocus></label>
          <label>Phone <input name="phone" value="{{ old('phone') }}" required></label>
          <label>Password <input name="password" type="password" required></label>
          <label class="check-row"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
          <button class="button primary" type="submit">Enter the dashboard</button>
        </form>

        <a class="field-note" href="{{ route('home') }}">← Back to the public site</a>
      </section>
    </main>
  </body>
</html>
