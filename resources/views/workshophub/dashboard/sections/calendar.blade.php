<section class="admin-panel">
  <div class="section-head">
    <h2>Calendar</h2>
    <div class="button-row">
      @foreach (['month' => 'Monthly', 'week' => 'Weekly', 'day' => 'Daily'] as $key => $label)
        <a class="segment {{ $range === $key ? 'is-active' : '' }}" href="{{ route('dashboard', ['section' => 'calendar', 'range' => $key, 'date' => $anchor->toDateString()]) }}">{{ $label }}</a>
      @endforeach
    </div>
  </div>

  <div class="button-row spaced">
    <a class="button" href="{{ route('dashboard', ['section' => 'calendar', 'range' => $range, 'date' => $anchor->copy()->sub(1, $range)->toDateString()]) }}">←</a>
    <b>{{ $range === 'month' ? $anchor->format('F Y') : $anchor->format('d M Y') }}</b>
    <a class="button" href="{{ route('dashboard', ['section' => 'calendar', 'range' => $range, 'date' => $anchor->copy()->add(1, $range)->toDateString()]) }}">→</a>
  </div>

  <div class="month-grid {{ $range === 'day' ? 'single' : '' }}">
    @php($cursor = $from->copy())
    @while ($cursor->lte($to))
      @php($date = $cursor->toDateString())
      <div class="month-cell {{ $cursor->isToday() ? 'is-today' : '' }} {{ $range === 'month' && $cursor->month !== $anchor->month ? 'is-muted' : '' }}">
        <span class="cell-date">{{ $cursor->format($range === 'month' ? 'j' : 'D j M') }}</span>
        @foreach ($calendarBookings->get($date, collect()) as $booking)
          <span class="cell-item booking">{{ substr((string) $booking->starts_at, 0, 5) }} {{ $booking->visitor_name }}</span>
        @endforeach
        @foreach ($calendarEvents->get($date, collect()) as $event)
          <span class="cell-item personal">
            {{ substr((string) $event->starts_at, 0, 5) ?: '·' }} {{ $event->title }}
            <form class="inline-form" method="post" action="{{ route('dashboard.events.delete', $event) }}">@csrf<button type="submit" title="Remove">×</button></form>
          </span>
        @endforeach
      </div>
      @php($cursor->addDay())
    @endwhile
  </div>
</section>

<section class="admin-panel">
  <h2>Add a personal event</h2>
  <p class="field-note">Personal events show on your calendar but never consume public booking slots.</p>
  <form class="form-grid" method="post" action="{{ route('dashboard.events.store') }}">
    @csrf
    <div class="form-row">
      <label>Title <input name="title" placeholder="Dentist, supplier visit…" required></label>
      <label>Date <input name="event_date" type="date" value="{{ $anchor->toDateString() }}" required></label>
    </div>
    <div class="form-row">
      <label>From <input name="starts_at" type="time"></label>
      <label>To <input name="ends_at" type="time"></label>
    </div>
    <button class="button primary" type="submit">Add event</button>
  </form>
</section>
