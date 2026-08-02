@php
  $used = $class->bookedSeats();
  $left = $class->seatsLeft();
  $percent = min(100, round(($used / max(1, $class->capacity)) * 100));
@endphp

<article class="class-card" data-class-category="{{ Str::slug($class->category) }}">
  <div class="class-card-header">
    <div><span class="tag">{{ $class->category }}</span><h3>{{ $class->title }}</h3></div>
    <span class="status-pill {{ $left > 0 ? 'approved' : 'waitlist' }}">{{ $left > 0 ? $left.' seats' : 'Waitlist' }}</span>
  </div>
  <p>{{ $class->summary }}</p>
  <ul class="meta-list">
    <li><b>Instructor:</b> {{ $class->instructor->name }}</li>
    <li><b>Schedule:</b> {{ $class->weekday }} {{ Str::of($class->time)->substr(0, 5) }}</li>
    <li><b>Level:</b> {{ $class->level }} in {{ $class->room }}</li>
  </ul>
  <div class="seat-meter">
    <div class="meter" aria-label="{{ $percent }} percent booked"><span style="width: {{ $percent }}%"></span></div>
    <span class="field-note">{{ $used }} of {{ $class->capacity }} seats requested</span>
  </div>
  <a class="button primary" href="{{ route('home', ['view' => 'booking', 'class' => $class->id]) }}">Choose class</a>
</article>
