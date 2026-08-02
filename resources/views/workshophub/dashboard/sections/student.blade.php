<section class="admin-panel">
  <div class="section-head">
    <h2>{{ $student->name }}</h2>
    <div class="button-row">
      <a class="button" href="{{ route('dashboard.students.waiver', $student) }}">Waiver PDF (filled)</a>
      <a class="button" href="{{ route('dashboard', ['section' => 'students']) }}">← All students</a>
    </div>
  </div>

  <form class="form-grid" method="post" action="{{ route('dashboard.students.update', $student) }}">
    @csrf
    <div class="form-row">
      <label>Name <input name="name" value="{{ $student->name }}" required></label>
      <label>Contact (phone) <input name="contact" value="{{ $student->contact }}" required></label>
    </div>
    <label class="check-row"><input type="checkbox" name="archived" value="1" @checked($student->archived)> Archive this student</label>
    <button class="button primary" type="submit">Save profile</button>
  </form>
</section>

<section class="admin-panel">
  <h2>Session records</h2>
  <form class="form-grid" method="post" action="{{ route('dashboard.students.records', $student) }}" enctype="multipart/form-data">
    @csrf
    <div class="form-row">
      <label>Title <input name="title" placeholder="Wheel session 3 — centering" required></label>
      <label>Date <input name="record_date" type="date" value="{{ now()->toDateString() }}" required></label>
    </div>
    <label>Notes
      <div class="wysiwyg" data-wysiwyg>
        <div class="wysiwyg-toolbar">
          <button type="button" data-cmd="bold"><b>B</b></button>
          <button type="button" data-cmd="italic"><i>I</i></button>
          <button type="button" data-cmd="insertUnorderedList">• list</button>
          <button type="button" data-cmd="formatBlock" data-value="h3">H</button>
        </div>
        <div class="wysiwyg-area" contenteditable="true" data-wysiwyg-area></div>
        <textarea name="content" class="is-hidden" data-wysiwyg-input></textarea>
      </div>
    </label>
    <label>Attachment (PDF or photo) <input name="attachment" type="file" accept=".pdf,.png,.jpg,.jpeg"></label>
    <button class="button primary" type="submit">Save session record</button>
  </form>

  <div class="post-list spaced">
    @forelse ($student->records->sortByDesc('record_date') as $record)
      <article class="post-card">
        <h3>{{ $record->title }}</h3>
        <span class="tag">{{ $record->record_date->format('Y-m-d') }}</span>
        @if ($record->content) <div class="rich-text">{!! $record->content !!}</div> @endif
        @if ($record->file_path)
          <a class="button" href="{{ asset($record->file_path) }}" target="_blank" rel="noopener">📎 {{ $record->file_name }}</a>
        @endif
      </article>
    @empty
      <p class="field-note">No session records yet — write the first one after the next class.</p>
    @endforelse
  </div>
</section>

<section class="admin-panel">
  <h2>Quick notes &amp; bookings</h2>
  <form class="form-row" method="post" action="{{ route('dashboard.students.notes', $student) }}">
    @csrf
    <input name="note" placeholder="Add a quick owner note" required>
    <button class="button" type="submit">Add note</button>
  </form>
  <ul class="meta-list spaced">
    @foreach ($student->notes as $note)<li>{{ $note->note }}</li>@endforeach
    @foreach ($student->bookings as $booking)
      <li><b>{{ $booking->booking_code }}</b> · {{ $booking->scheduled_date->format('Y-m-d') }} {{ substr((string) $booking->starts_at, 0, 5) }} <span class="status-pill {{ $booking->status }}">{{ $booking->status }}</span></li>
    @endforeach
  </ul>
</section>
