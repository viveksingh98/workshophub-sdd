<section class="admin-panel">
  <div class="section-head">
    <h2>Students and their story</h2>
    <a class="button" href="{{ route('dashboard', ['section' => 'students', 'archived' => $showArchived ? 0 : 1]) }}">
      {{ $showArchived ? 'Hide archived' : 'Show archived' }}
    </a>
  </div>
  <p class="field-note">Every public booking creates a profile automatically — phone number is the identifier.</p>

  <div class="student-list spaced">
    @forelse ($students as $student)
      <article class="student-card">
        <div class="section-head">
          <h3>{{ $student->name }} @if ($student->archived) <span class="tag">archived</span> @endif</h3>
          <a class="button" href="{{ route('dashboard', ['section' => 'student', 'id' => $student->id]) }}">Open profile</a>
        </div>
        <span class="field-note">{{ $student->contact }} · {{ $student->bookings->count() }} booking{{ $student->bookings->count() === 1 ? '' : 's' }} · {{ $student->records->count() }} session record{{ $student->records->count() === 1 ? '' : 's' }}</span>
      </article>
    @empty
      <p class="field-note">No students yet — they appear automatically with the first booking.</p>
    @endforelse
  </div>
</section>
