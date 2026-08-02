<form class="form-grid" method="post" action="{{ route('bookings.store') }}" data-booking-form>
  @csrf
  <label>Class
    <select name="workshop_class_id" required>
      @foreach ($classes as $class)
        <option value="{{ $class->id }}" @selected((int) request('class') === $class->id)>{{ $class->title }}</option>
      @endforeach
    </select>
  </label>
  <div class="form-row">
    <label>Name <input name="visitor_name" value="{{ old('visitor_name', 'Aarav Mehta') }}" required></label>
    <label>Contact <input name="contact" value="{{ old('contact', 'aarav@example.com') }}" required></label>
  </div>
  <div class="form-row">
    <label>Date <input name="scheduled_date" type="date" value="{{ old('scheduled_date', now()->addDays(7)->toDateString()) }}" required></label>
    <label>Seats <input name="seats" type="number" min="1" max="3" value="{{ old('seats', 1) }}" required></label>
  </div>
  <label>Note <textarea name="note" placeholder="Accessibility needs, prior experience, or class goal">{{ old('note') }}</textarea></label>
  <button class="button primary" type="submit">Create booking request</button>
  <div class="field-note">Server validation checks required fields, contact format, seat count, and remaining capacity.</div>
</form>
