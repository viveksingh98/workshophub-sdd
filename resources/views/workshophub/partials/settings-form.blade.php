<form class="form-grid" method="post" action="{{ route('admin.settings.update') }}">
  @csrf
  <div class="form-row">
    <label>Studio name <input name="studio_name" value="{{ $settings['studio_name'] }}" required></label>
    <label>Owner name <input name="owner_name" value="{{ $settings['owner_name'] }}" required></label>
  </div>
  <div class="form-row">
    <label>Logo text <input name="logo_text" maxlength="3" value="{{ $settings['logo_text'] }}" required></label>
    <label>Contact email <input name="contact_email" type="email" value="{{ $settings['contact_email'] }}" required></label>
  </div>
  <label>Tagline <input name="tagline" value="{{ $settings['tagline'] }}" required></label>
  <label>Address <input name="address" value="{{ $settings['address'] }}" required></label>
  <label>Public phrase <textarea name="hero_message" required>{{ $settings['hero_message'] }}</textarea></label>
  <div class="form-row">
    <label>Social link <input name="social_links" value="{{ $settings['social_links'] }}" required></label>
    <label>Email subject <input name="email_subject" value="{{ $settings['email_subject'] }}" required></label>
  </div>
  <button class="button primary" type="submit">Save settings</button>
</form>
