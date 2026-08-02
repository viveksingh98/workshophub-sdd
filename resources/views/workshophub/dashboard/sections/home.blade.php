<section class="admin-panel">
  <h2>Today at {{ $settings['studio_name'] }}</h2>
  <div class="dashboard-grid">
    <div class="dashboard-tile"><span>Upcoming bookings</span><strong>{{ $metrics['upcoming'] }}</strong></div>
    <div class="dashboard-tile"><span>Students</span><strong>{{ $metrics['students'] }}</strong></div>
    <div class="dashboard-tile"><span>Articles</span><strong>{{ $metrics['articles'] }}</strong></div>
    <div class="dashboard-tile"><span>Session records</span><strong>{{ $metrics['sessions'] }}</strong></div>
  </div>
</section>

<section class="admin-panel">
  <h2>Today's schedule</h2>
  @if ($todaysBookings->isEmpty() && $todaysEvents->isEmpty())
    <p class="field-note">Nothing on the calendar today — a quiet studio day. New bookings land here automatically.</p>
  @else
    <ul class="meta-list">
      @foreach ($todaysBookings as $booking)
        <li><b>{{ substr((string) $booking->starts_at, 0, 5) ?: 'Class' }}</b> · {{ $booking->visitor_name }} · {{ $booking->mode === 'online' ? 'Online' : 'In-studio' }} <span class="status-pill {{ $booking->status }}">{{ $booking->status }}</span></li>
      @endforeach
      @foreach ($todaysEvents as $event)
        <li><b>{{ substr((string) $event->starts_at, 0, 5) ?: 'All day' }}</b> · {{ $event->title }} <span class="tag">personal</span></li>
      @endforeach
    </ul>
  @endif
</section>
