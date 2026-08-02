<div class="section-head">
  <div>
    <h2>SDD source map</h2>
    <p>Trace the demo to the script units used in your tutorial.</p>
  </div>
</div>

<div class="two-column">
  <section class="panel">
    <h2>Script evidence</h2>
    <div class="phase-list">
      @foreach ([
        'Unit 04' => 'Public pages, class calendar, instructor dashboard, student signups, blog, and email notifications.',
        'Unit 23' => 'ClassController, BookingRequest, Student model, and dashboard routes.',
        'Unit 29' => 'Laravel, MySQL, server-rendered pages, native JavaScript, and theme CSS folders.',
        'Unit 34' => 'Setup wizard with owner name, studio logo, contact email, categories, schedule defaults, and theme.',
        'Unit 35' => 'Owner login, dashboard metrics, availability, and booking management.',
        'Unit 36' => 'Calendar, class articles, student profiles, and automatic student creation from bookings.',
        'Unit 37' => 'Student notes, waiver templates, FAQ pages, and downloadable documents.',
        'Unit 38' => 'Public phrases, social links, email settings, theme selection, and image management.',
        'Unit 39' => 'Landing page, class list, booking form, blog, FAQ, map, and contact buttons.',
        'Unit 50' => 'PHP version, MySQL, SSL, cron, email support, backups, and file permissions.',
      ] as $unit => $detail)
        <article class="phase-card"><h3>{{ $unit }}</h3><p>{{ $detail }}</p></article>
      @endforeach
    </div>
  </section>
  <section class="panel">
    <h2>Acceptance checks</h2>
    <div class="phase-list">
      @foreach ([
        'Visitor can browse classes, instructors, FAQ, blog, and contact actions.',
        'BookingRequest validates required fields, contact format, date, seat count, and class capacity.',
        'A booking creates or updates a student profile and note.',
        'Owner can view metrics and change booking status.',
        'Owner can add classes, notes, posts, settings, and theme values.',
        'Theme CSS folders switch the public/admin palette without rebuilding assets.',
        'Docker Compose documents the MySQL runtime path while local SQLite can validate when Docker is off.',
      ] as $check)
        <article class="phase-card">{{ $check }}</article>
      @endforeach
    </div>
    <a class="button primary" href="{{ route('documents.waiver') }}">Download waiver note</a>
  </section>
</div>
