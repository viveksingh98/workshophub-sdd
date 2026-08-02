<div class="section-head">
  <div>
    <h2>Public class discovery</h2>
    <p>Homepage, class list, instructor cards, FAQ, blog, map, contact buttons, and booking entry point.</p>
  </div>
  <div class="filter-row" data-class-filter>
    <button class="segment is-active" type="button" data-category="all">All</button>
    @foreach ($categories as $category)
      <button class="segment" type="button" data-category="{{ Str::slug($category) }}">{{ $category }}</button>
    @endforeach
  </div>
</div>

<div class="two-column">
  <section class="grid" aria-label="Class cards">
    <div class="class-grid">
      @foreach ($classes as $class)
        @include('workshophub.partials.class-card', ['class' => $class])
      @endforeach
    </div>
  </section>
  <aside class="panel">
    <h2>Book a demo seat</h2>
    @include('workshophub.partials.booking-form')
  </aside>
</div>

<div class="three-column">
  <section class="panel">
    <h2>Instructors</h2>
    <div class="post-list">
      @foreach ($instructors as $instructor)
        <article class="post-card">
          <span class="avatar">{{ $instructor->image_label }}</span>
          <h3>{{ $instructor->name }}</h3>
          <p>{{ $instructor->bio }}</p>
          <span class="tag">{{ $instructor->expertise }}</span>
        </article>
      @endforeach
    </div>
  </section>

  <section class="panel">
    <h2>FAQ</h2>
    <div class="faq-list">
      @foreach ($faqs as $faq)
        <article class="faq-item"><h3>{{ $faq->question }}</h3><p>{{ $faq->answer }}</p></article>
      @endforeach
    </div>
  </section>

  <section class="panel">
    <h2>Studio blog</h2>
    <div class="post-list">
      @foreach ($posts->where('status', 'Published') as $post)
        <article class="post-card"><h3>{{ $post->title }}</h3><p>{{ $post->excerpt }}</p></article>
      @endforeach
    </div>
  </section>
</div>

<section class="panel">
  <div class="contact-grid">
    <div>
      <h2>Visit and contact</h2>
      <p class="help-text">{{ $settings['address'] }}</p>
      <div class="button-row">
        <a class="button primary" href="mailto:{{ $settings['contact_email'] }}">Email studio</a>
        <button class="button" type="button" data-copy="{{ $settings['address'] }}">Copy address</button>
        <a class="button" href="{{ route('documents.waiver') }}">Download waiver</a>
      </div>
    </div>
    <div class="map-preview" aria-label="Simple studio area map">
      <span class="map-road road-a"></span>
      <span class="map-road road-b"></span>
      <span class="map-pin"><b>{{ $settings['logo_text'] }}</b></span>
    </div>
  </div>
</section>
