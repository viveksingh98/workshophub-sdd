<div class="section-head">
  <div>
    <h2>Owner workspace</h2>
    <p>Dashboard metrics, bookings, students, content, availability, themes, and settings.</p>
  </div>
  @if ($ownerUnlocked)
    <form method="post" action="{{ route('owner.logout') }}">@csrf<button class="button" type="submit">Lock owner view</button></form>
  @endif
</div>

@unless ($ownerUnlocked)
  <section class="panel narrow">
    <h2>Owner sign in</h2>
    <form class="form-grid" method="post" action="{{ route('owner.login') }}">
      @csrf
      <label>Email <input name="email" type="email" value="{{ $settings['contact_email'] }}" required></label>
      <label>Demo passcode <input name="passcode" type="password" value="studio-demo" minlength="6" required></label>
      <button class="button primary" type="submit">Enter owner workspace</button>
    </form>
  </section>
@else
  <div class="admin-shell">
    <aside class="admin-nav" data-admin-nav>
      @foreach (['dashboard', 'bookings', 'classes', 'students', 'content', 'availability', 'themes', 'settings'] as $tab)
        <button class="segment {{ $loop->first ? 'is-active' : '' }}" type="button" data-admin-tab="{{ $tab }}">{{ Str::title($tab) }}</button>
      @endforeach
    </aside>

    <div class="admin-content">
      <section class="admin-panel" data-admin-panel="dashboard">
        <h2>Dashboard metrics</h2>
        <div class="dashboard-grid">
          <div class="dashboard-tile"><span>Active bookings</span><strong>{{ $metrics['bookings'] }}</strong></div>
          <div class="dashboard-tile"><span>Pending review</span><strong>{{ $metrics['pending'] }}</strong></div>
          <div class="dashboard-tile"><span>Approved seats</span><strong>{{ $metrics['approvedSeats'] }}</strong></div>
          <div class="dashboard-tile"><span>Students</span><strong>{{ $metrics['students'] }}</strong></div>
          <div class="dashboard-tile"><span>Classes</span><strong>{{ $metrics['classes'] }}</strong></div>
          <div class="dashboard-tile"><span>Blog posts</span><strong>{{ $posts->count() }}</strong></div>
        </div>
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="bookings">
        <h2>Booking management</h2>
        @include('workshophub.partials.booking-table', ['compact' => false])
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="classes">
        <h2>Classes</h2>
        <form class="form-grid" method="post" action="{{ route('admin.classes.store') }}">
          @csrf
          <div class="form-row">
            <label>Title <input name="title" placeholder="Mosaic Night" required></label>
            <label>Category <input name="category" placeholder="Ceramics" required></label>
          </div>
          <div class="form-row">
            <label>Instructor <input name="instructor_name" placeholder="Instructor name" required></label>
            <label>Room <input name="room" placeholder="Studio room" required></label>
          </div>
          <div class="form-row">
            <label>Weekday
              <select name="weekday">@foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)<option>{{ $day }}</option>@endforeach</select>
            </label>
            <label>Time <input name="time" type="time" value="18:00" required></label>
          </div>
          <div class="form-row">
            <label>Capacity <input name="capacity" type="number" min="1" value="8" required></label>
            <label>Duration <input name="duration_minutes" type="number" min="30" step="15" value="90" required></label>
          </div>
          <div class="form-row">
            <label>Level <input name="level" value="Mixed" required></label>
            <label>Summary <input name="summary" value="Short practical class description." required></label>
          </div>
          <button class="button primary" type="submit">Add class</button>
        </form>
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="students">
        <h2>Students and notes</h2>
        <div class="student-list">
          @foreach ($students as $student)
            <article class="student-card">
              <h3>{{ $student->name }}</h3>
              <span class="field-note">{{ $student->contact }} - {{ $student->bookings->count() }} booking{{ $student->bookings->count() === 1 ? '' : 's' }}</span>
              <ul class="meta-list">@foreach ($student->notes as $note)<li>{{ $note->note }}</li>@endforeach</ul>
              <form class="button-row" method="post" action="{{ route('admin.students.notes.store', $student) }}">
                @csrf
                <input name="note" placeholder="Add owner note" required>
                <button class="button" type="submit">Add note</button>
              </form>
            </article>
          @endforeach
        </div>
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="content">
        <h2>Content, policies, and FAQ</h2>
        <form class="form-grid" method="post" action="{{ route('admin.posts.store') }}">
          @csrf
          <label>Post title <input name="title" placeholder="New studio update" required></label>
          <label>Excerpt <textarea name="excerpt" required>Write a short public update for visitors.</textarea></label>
          <label>Status <select name="status"><option>Draft</option><option>Published</option></select></label>
          <button class="button primary" type="submit">Add post</button>
        </form>
        <div class="post-list spaced">
          @foreach ($posts as $post)
            <article class="post-card"><h3>{{ $post->title }}</h3><span class="tag">{{ $post->status }}</span><p>{{ $post->excerpt }}</p></article>
          @endforeach
        </div>
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="availability">
        <h2>Weekly availability</h2>
        <div class="calendar-grid">
          @foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day)
            <div class="day-column">
              <h3>{{ $day }}</h3>
              @forelse ($classes->where('weekday', $day) as $class)
                <div class="class-chip"><b>{{ Str::of($class->time)->substr(0, 5) }} {{ $class->title }}</b><br>{{ $class->instructor->name }}<br>{{ $class->seatsLeft() }} open seats</div>
              @empty
                <div class="class-chip">No classes</div>
              @endforelse
            </div>
          @endforeach
        </div>
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="themes">
        <h2>Themes</h2>
        <div class="theme-row">
          @foreach ($themes as $key => $label)
            <form method="post" action="{{ route('admin.theme.update') }}">
              @csrf
              <input type="hidden" name="theme" value="{{ $key }}">
              <button class="theme-swatch {{ $settings['theme'] === $key ? 'is-active' : '' }}" type="submit"><span class="swatch swatch-{{ $key }}"></span>{{ $label }}</button>
            </form>
          @endforeach
        </div>
      </section>

      <section class="admin-panel is-hidden" data-admin-panel="settings">
        <h2>Settings</h2>
        @include('workshophub.partials.settings-form')
      </section>
    </div>
  </div>
@endunless
