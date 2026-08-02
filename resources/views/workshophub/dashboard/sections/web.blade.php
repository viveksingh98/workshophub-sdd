<section class="admin-panel">
  <h2>Themes</h2>
  <p class="field-note">Preview opens in a new tab with your real data; activate switches the public site instantly.</p>
  <div class="theme-row">
    @foreach ($themes as $key => $label)
      <div class="theme-card">
        <span class="swatch swatch-{{ $key }}"></span>
        <b>{{ $label }}</b>
        <div class="button-row">
          <a class="button" href="{{ route('theme.preview', $key) }}" target="_blank" rel="noopener">Preview</a>
          <form method="post" action="{{ route('dashboard.theme') }}">
            @csrf
            <input type="hidden" name="theme" value="{{ $key }}">
            <button class="button {{ $settings['theme'] === $key ? 'primary' : '' }}" type="submit">{{ $settings['theme'] === $key ? 'Active' : 'Activate' }}</button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
</section>

<section class="admin-panel">
  <h2>Images</h2>
  <p class="field-note">Uploads override the theme defaults on the public site.</p>
  <form class="form-grid" method="post" action="{{ route('dashboard.images') }}" enctype="multipart/form-data">
    @csrf
    <div class="form-row">
      <label>Slot
        <select name="slot">
          <option value="hero_image">Homepage hero image</option>
          <option value="logo_image">Logo</option>
          <option value="favicon">Favicon</option>
        </select>
      </label>
      <label>Image <input name="image" type="file" accept="image/*" required></label>
    </div>
    <button class="button primary" type="submit">Upload &amp; override</button>
  </form>
</section>

<section class="admin-panel">
  <h2>Public phrases, contact &amp; social</h2>
  <form class="form-grid" method="post" action="{{ route('dashboard.settings') }}">
    @csrf
    <div class="form-row">
      <label>Studio name <input name="studio_name" value="{{ $settings['studio_name'] }}" required></label>
      <label>Owner name <input name="owner_name" value="{{ $settings['owner_name'] }}" required></label>
    </div>
    <div class="form-row">
      <label>Logo text <input name="logo_text" maxlength="3" value="{{ $settings['logo_text'] }}" required></label>
      <label>Tagline <input name="tagline" value="{{ $settings['tagline'] }}" required></label>
    </div>
    <label>Hero message <textarea name="hero_message" required>{{ $settings['hero_message'] }}</textarea></label>
    <label>Meet-the-studio text <textarea name="meet_the_studio">{{ $settings['meet_the_studio'] ?? '' }}</textarea></label>
    <div class="form-row">
      <label>Contact email <input name="contact_email" type="email" value="{{ $settings['contact_email'] }}" required></label>
      <label>Contact phone <input name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}"></label>
    </div>
    <div class="form-row">
      <label>WhatsApp number <input name="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}"></label>
      <label>Address <input name="address" value="{{ $settings['address'] }}" required></label>
    </div>
    <div class="form-row">
      <label>Instagram URL <input name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}"></label>
      <label>YouTube URL <input name="social_youtube" value="{{ $settings['social_youtube'] ?? '' }}"></label>
    </div>
    <label>Facebook URL <input name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}"></label>

    <h3>Email notifications (Gmail)</h3>
    <p class="field-note">Use a Gmail address + an <b>app password</b> (Google Account → Security → 2-Step Verification → App passwords). New bookings then email you for exactly zero dollars.</p>
    <div class="form-row">
      <label>Gmail address <input name="gmail_username" type="email" value="{{ $settings['gmail_username'] ?? '' }}"></label>
      <label>Gmail app password <input name="gmail_app_password" type="password" value="{{ $settings['gmail_app_password'] ?? '' }}"></label>
    </div>
    <label>Notify email (defaults to the Gmail address) <input name="notify_email" type="email" value="{{ $settings['notify_email'] ?? '' }}"></label>

    <h3>Waiver template</h3>
    <p class="field-note">Variables: <code>@{{student_name}}</code> <code>@{{student_contact}}</code> <code>@{{date}}</code> <code>@{{studio_name}}</code> <code>@{{owner_name}}</code> · <a href="{{ route('dashboard.waiver.blank') }}">download the blank PDF</a></p>
    <label>Template <textarea name="waiver_template" rows="8">{{ $settings['waiver_template'] ?? '' }}</textarea></label>

    <button class="button primary" type="submit">Save web settings</button>
  </form>
</section>
