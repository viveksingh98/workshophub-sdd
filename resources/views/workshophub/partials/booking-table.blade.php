<div class="table-shell">
  <table>
    <thead>
      <tr>
        <th>Booking</th><th>Visitor</th><th>Class</th><th>Status</th><th>Note</th>@unless ($compact)<th>Actions</th>@endunless
      </tr>
    </thead>
    <tbody>
      @forelse ($bookings->take($compact ? 5 : 100) as $booking)
        <tr>
          <td><b>{{ $booking->booking_code }}</b><br><span class="field-note">{{ $booking->scheduled_date->format('Y-m-d') }}</span></td>
          <td>{{ $booking->visitor_name }}<br><span class="field-note">{{ $booking->contact }}</span></td>
          <td>{{ $booking->workshopClass->title }}<br><span class="field-note">{{ $booking->seats }} seat{{ $booking->seats === 1 ? '' : 's' }}</span></td>
          <td><span class="status-pill {{ $booking->status }}">{{ Str::title($booking->status) }}</span></td>
          <td>{{ $booking->note ?: 'No note' }}</td>
          @unless ($compact)
            <td>
              <div class="button-row">
                @foreach (['approved' => 'Approve', 'waitlist' => 'Waitlist', 'cancelled' => 'Cancel'] as $status => $label)
                  <form method="post" action="{{ route('admin.bookings.status', $booking) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $status }}">
                    <button class="button {{ $status === 'cancelled' ? 'danger' : '' }}" type="submit">{{ $label }}</button>
                  </form>
                @endforeach
              </div>
            </td>
          @endunless
        </tr>
      @empty
        <tr><td colspan="{{ $compact ? 5 : 6 }}">No bookings yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
