<section class="admin-panel">
  <h2>Every booking, one row</h2>
  <form class="filter-row" method="get" action="{{ route('dashboard') }}">
    <input type="hidden" name="section" value="bookings">
    <select name="status" onchange="this.form.submit()">
      <option value="">All statuses</option>
      @foreach (['pending', 'approved', 'waitlist', 'cancelled'] as $status)
        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
      @endforeach
    </select>
  </form>

  <div class="table-shell spaced">
    <table>
      <thead><tr><th>Code</th><th>Student</th><th>Mode</th><th>When</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse ($bookings as $booking)
          <tr>
            <td><b>{{ $booking->booking_code }}</b></td>
            <td>{{ $booking->visitor_name }}<br><span class="field-note">{{ $booking->contact }}</span></td>
            <td>{{ $booking->mode === 'online' ? 'Online' : 'In-studio' }}</td>
            <td>{{ $booking->scheduled_date->format('Y-m-d') }} {{ substr((string) $booking->starts_at, 0, 5) }}</td>
            <td><span class="status-pill {{ $booking->status }}">{{ $booking->status }}</span></td>
            <td>
              <form class="form-grid compact" method="post" action="{{ route('dashboard.bookings.update', $booking) }}">
                @csrf
                <div class="form-row">
                  <select name="status">
                    @foreach (['pending', 'approved', 'waitlist', 'cancelled'] as $status)
                      <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                  </select>
                  <button class="button" type="submit">Save</button>
                </div>
                <div class="form-row">
                  <input name="scheduled_date" type="date" value="{{ $booking->scheduled_date->format('Y-m-d') }}" title="Reschedule date">
                  <input name="starts_at" type="time" value="{{ substr((string) $booking->starts_at, 0, 5) }}" title="Reschedule time">
                </div>
              </form>
              <a class="button" target="_blank" rel="noopener"
                 href="https://wa.me/{{ preg_replace('/\D+/', '', $booking->contact) }}?text={{ rawurlencode('Reminder: your '.($booking->mode === 'online' ? 'online' : 'in-studio').' class on '.$booking->scheduled_date->format('Y-m-d').' at '.substr((string) $booking->starts_at, 0, 5).' — '.$settings['studio_name']) }}">
                WhatsApp reminder
              </a>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="field-note">No bookings match this filter yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

<section class="admin-panel">
  <h2>Add a booking manually</h2>
  <p class="field-note">Type a name — existing students autofill their details (phone is the identifier).</p>
  <form class="form-grid" method="post" action="{{ route('dashboard.bookings.store') }}">
    @csrf
    <div class="form-row">
      <label>Student name
        <input name="visitor_name" autocomplete="off" data-student-search data-search-url="{{ route('dashboard.students.search') }}" required>
        <div class="autofill-list is-hidden" data-student-results></div>
      </label>
      <label>Phone <input name="phone" data-student-phone required></label>
    </div>
    <div class="form-row">
      <label>Mode
        <select name="mode">@foreach ($modes as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select>
      </label>
      <label>Date <input name="scheduled_date" type="date" value="{{ now()->addDay()->toDateString() }}" required></label>
    </div>
    <label>Time <input name="starts_at" type="time" value="10:00" required></label>
    <label>Note <textarea name="note" placeholder="Booked over the phone / WhatsApp / in person"></textarea></label>
    <button class="button primary" type="submit">Create booking</button>
  </form>
</section>
