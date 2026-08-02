<div class="section-head">
  <div>
    <h2>Book a class in one minute</h2>
    <p>The calendar only offers days the studio actually opened — pick a mode, a day, and a free slot.</p>
  </div>
</div>

@if (empty($openDates['in_studio']) && empty($openDates['online']))
  <section class="panel narrow">
    <h2>Booking is paused</h2>
    <p class="field-note">The studio is on holiday right now — check back soon, or reach us with the contact buttons below.</p>
  </section>
@else
  <div class="two-column">
    <section class="panel">
      <h2>Your booking</h2>
      <form class="form-grid" method="post" action="{{ route('bookings.store') }}"
            data-booking-flow data-options-url="{{ route('booking.options') }}"
            data-open-dates='@json($openDates)'>
        @csrf
        <label>Class mode
          <select name="mode" data-booking-mode>
            @foreach ($modes as $key => $label)
              <option value="{{ $key }}" @selected(old('mode') === $key)>{{ $label }}</option>
            @endforeach
          </select>
        </label>
        <div class="form-row">
          <label>Day
            <select name="scheduled_date" data-booking-date required></select>
          </label>
          <label>Time slot
            <select name="starts_at" data-booking-slot required></select>
          </label>
        </div>
        <div class="form-row">
          <label>Your name <input name="visitor_name" value="{{ old('visitor_name') }}" required></label>
          <label>Phone number <input name="phone" inputmode="numeric" value="{{ old('phone') }}" placeholder="digits only" required></label>
        </div>
        <label>Reason / note (optional) <textarea name="note" placeholder="What would you like to work on?">{{ old('note') }}</textarea></label>
        @if (is_array($captcha))
          <label>Security question: what is {{ $captcha[0] }} + {{ $captcha[1] }}? <input name="security_answer" inputmode="numeric" required></label>
        @endif
        <button class="button primary" type="submit">Confirm booking</button>
        <div class="field-note">Phone numbers are cleaned automatically — spaces and dashes never survive. Rate-limited so nobody books eight hundred slots for fun.</div>
      </form>
    </section>

    <aside class="panel">
      <h2>Where we are</h2>
      <p class="help-text">{{ $settings['address'] }}</p>
      <div class="map-preview" aria-label="Simple studio area map">
        <span class="map-road road-a"></span>
        <span class="map-road road-b"></span>
        <span class="map-pin"><b>{{ $settings['logo_text'] }}</b></span>
      </div>
      <div class="button-row spaced">
        @if (! empty($settings['contact_phone'])) <a class="button" href="tel:{{ preg_replace('/[^\d+]/', '', $settings['contact_phone']) }}">📞 {{ $settings['contact_phone'] }}</a> @endif
        <a class="button" href="mailto:{{ $settings['contact_email'] }}">✉️ Email us</a>
      </div>
      @if (! empty($settings['pricing_in_studio']) || ! empty($settings['pricing_online']))
        <h3>Pricing</h3>
        <ul class="meta-list">
          @if (! empty($settings['pricing_in_studio'])) <li><b>In-studio:</b> {{ $settings['pricing_in_studio'] }}</li> @endif
          @if (! empty($settings['pricing_online'])) <li><b>Online:</b> {{ $settings['pricing_online'] }}</li> @endif
        </ul>
      @endif
    </aside>
  </div>
@endif
