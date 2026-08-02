<div class="section-head">
  <div>
    <h2>Classes at the studio</h2>
    <p>Hands-on classes across {{ $categories->join(', ', ' and ') }} — filter by what you want to make.</p>
  </div>
  <div class="filter-row" data-class-filter>
    <button class="segment is-active" type="button" data-category="all">All</button>
    @foreach ($categories as $category)
      <button class="segment" type="button" data-category="{{ Str::slug($category) }}">{{ $category }}</button>
    @endforeach
  </div>
</div>

<div class="class-grid">
  @foreach ($classes as $class)
    @include('workshophub.partials.class-card', ['class' => $class])
  @endforeach
</div>

<div class="two-column">
  <section class="panel">
    <h2>Meet the studio</h2>
    <p>{{ $settings['meet_the_studio'] ?? 'A community space where makers learn side by side — small groups, real tools, and instructors who love teaching.' }}</p>
    <div class="post-list spaced">
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
    <h2>Frequently asked</h2>
    <div class="faq-accordion">
      @foreach ($faqs as $faq)
        <details class="faq-item">
          <summary>{{ $faq->question }}</summary>
          <p>{{ $faq->answer }}</p>
        </details>
      @endforeach
    </div>

    <h2 class="spaced">From the blog</h2>
    <div class="post-list">
      @foreach ($posts->take(3) as $post)
        <article class="post-card">
          <h3><a href="{{ route('home', ['view' => 'blog', 'post' => $post->slug]) }}">{{ $post->title }}</a></h3>
          <p>{{ $post->excerpt }}</p>
        </article>
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
        <a class="button primary" href="{{ route('home', ['view' => 'booking']) }}">Book a class</a>
        @if (! empty($settings['whatsapp_number']))
          <a class="button" href="https://wa.me/{{ preg_replace('/\D+/', '', $settings['whatsapp_number']) }}" target="_blank" rel="noopener">💬 WhatsApp</a>
        @endif
        @if (! empty($settings['contact_phone']))
          <a class="button" href="tel:{{ preg_replace('/[^\d+]/', '', $settings['contact_phone']) }}">📞 Call</a>
        @endif
        <a class="button" href="mailto:{{ $settings['contact_email'] }}">✉️ Email</a>
      </div>
    </div>
    <div class="map-preview" aria-label="Simple studio area map">
      <span class="map-road road-a"></span>
      <span class="map-road road-b"></span>
      <span class="map-pin"><b>{{ $settings['logo_text'] }}</b></span>
    </div>
  </div>
</section>
