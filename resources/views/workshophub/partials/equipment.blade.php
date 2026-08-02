<div class="section-head">
  <div>
    <h2>Equipment reservation</h2>
    <p>The feature built in the Spec Kit videos: time-window reservations, an overlap guard inside a database transaction, and creator-only cancellation.</p>
  </div>
</div>

<div class="two-column">
  <section class="panel">
    <h2>Reserve equipment</h2>
    <form class="form-grid" method="post" action="{{ route('reservations.store') }}">
      @csrf
      <label>Equipment
        <select name="equipment_id" required>
          @foreach ($equipment as $item)
            <option value="{{ $item->id }}" @selected((int) old('equipment_id') === $item->id)>{{ $item->name }} — {{ $item->category }}</option>
          @endforeach
        </select>
      </label>
      <div class="form-row">
        <label>Name <input name="member_name" value="{{ old('member_name') }}" required></label>
        <label>Contact <input name="contact" value="{{ old('contact') }}" placeholder="email or phone" required></label>
      </div>
      <div class="form-row">
        <label>Date <input name="reserved_date" type="date" value="{{ old('reserved_date') }}" required></label>
        <label>From <input name="starts_at" type="time" value="{{ old('starts_at') }}" required></label>
        <label>To <input name="ends_at" type="time" value="{{ old('ends_at') }}" required></label>
      </div>
      <button class="button primary" type="submit">Reserve time slot</button>
      <div class="field-note">Overlapping reservations for the same equipment are rejected inside a locked database transaction — two members can never hold the same slot.</div>
    </form>
  </section>

  <section class="panel">
    <h2>Reservations</h2>
    <form class="form-grid" method="get" action="{{ route('home') }}">
      <input type="hidden" name="view" value="equipment">
      <div class="form-row">
        <label>Equipment
          <select name="equipment_filter">
            <option value="">All equipment</option>
            @foreach ($equipment as $item)
              <option value="{{ $item->id }}" @selected($reservationFilters['equipment'] == $item->id)>{{ $item->name }}</option>
            @endforeach
          </select>
        </label>
        <label>Day <input name="date_filter" type="date" value="{{ $reservationFilters['date'] }}"></label>
      </div>
      <button class="button ghost" type="submit">Filter list</button>
    </form>

    @forelse ($reservations as $reservation)
      <article class="post-card">
        <div>
          <strong>{{ $reservation->reservation_code }}</strong> · {{ $reservation->equipment->name }}
          <span class="status-pill">{{ $reservation->status }}</span>
        </div>
        <p>{{ $reservation->member_name }} · {{ $reservation->reserved_date->format('Y-m-d') }} · {{ $reservation->starts_at }}–{{ $reservation->ends_at }}</p>
        @unless ($reservation->isCancelled())
          <form class="form-row" method="post" action="{{ route('reservations.cancel', $reservation) }}">
            @csrf
            <label>Cancel with your contact <input name="cancel_contact" placeholder="contact used when reserving" required></label>
            <button class="button ghost" type="submit">Cancel</button>
          </form>
        @endunless
      </article>
    @empty
      <p class="field-note">No reservations match this filter yet — create the first one from the form.</p>
    @endforelse
  </section>
</div>
