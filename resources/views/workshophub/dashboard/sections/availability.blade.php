<section class="admin-panel">
  <h2>Availability runs the studio</h2>
  <p class="field-note">Durations, the weekly grid, and holiday periods drive every slot a visitor can book.</p>

  <form class="form-grid" method="post" action="{{ route('dashboard.availability') }}">
    @csrf
    <div class="form-row">
      <label>In-studio class duration (min) <input name="class_duration" type="number" min="15" max="240" value="{{ $config['class_duration'] }}" required></label>
      <label>Online class duration (min) <input name="online_duration" type="number" min="15" max="240" value="{{ $config['online_duration'] }}" required></label>
    </div>
    <div class="form-row">
      <label>Break between sessions (min) <input name="break_minutes" type="number" min="0" max="120" value="{{ $config['break_minutes'] }}" required></label>
      <label>Days ahead students can book <input name="advance_days" type="number" min="1" max="120" value="{{ $config['advance_days'] }}" required></label>
    </div>
    <div class="form-row">
      <label>Day starts <input name="day_start" type="time" value="{{ $config['day_start'] }}" required></label>
      <label>Day ends <input name="day_end" type="time" value="{{ $config['day_end'] }}" required></label>
    </div>

    <h3>Weekly grid</h3>
    <div class="week-grid">
      @foreach ($config['week'] as $day => $mode)
        <label class="week-day">{{ $day }}
          <select name="week[{{ $day }}]">
            <option value="in_studio" @selected($mode === 'in_studio')>In-studio</option>
            <option value="online" @selected($mode === 'online')>Online</option>
            <option value="closed" @selected($mode === 'closed')>Closed</option>
          </select>
        </label>
      @endforeach
    </div>

    <label class="check-row"><input type="checkbox" name="holiday_mode" value="1" @checked($holidayMode)> Holiday mode — pause all public booking</label>
    <button class="button primary" type="submit">Save availability</button>
    <div class="field-note">Slot math: duration + break. 50-minute classes with a 10-minute break = bookable every 60 minutes.</div>
  </form>
</section>

<section class="admin-panel">
  <h2>Holiday periods</h2>
  <form class="form-grid" method="post" action="{{ route('dashboard.holidays.store') }}">
    @csrf
    <div class="form-row">
      <label>From <input name="starts_on" type="date" required></label>
      <label>To <input name="ends_on" type="date" required></label>
    </div>
    <button class="button primary" type="submit">Add holiday period</button>
  </form>
  <ul class="meta-list spaced">
    @forelse ($holidays as $holiday)
      <li>
        <b>{{ $holiday->starts_on->format('Y-m-d') }} → {{ $holiday->ends_on->format('Y-m-d') }}</b> — blocked on the public calendar
        <form class="inline-form" method="post" action="{{ route('dashboard.holidays.delete', $holiday) }}">@csrf<button class="button danger" type="submit">Remove</button></form>
      </li>
    @empty
      <li class="field-note">No holiday periods yet — add as many as you need.</li>
    @endforelse
  </ul>
</section>
