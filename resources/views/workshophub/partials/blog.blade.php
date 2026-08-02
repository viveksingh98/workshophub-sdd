@if ($activePost)
  <article class="panel narrow article-page">
    <a class="field-note" href="{{ route('home', ['view' => 'blog']) }}">← All posts</a>
    <h1>{{ $activePost->title }}</h1>
    <p class="field-note"><span class="tag">{{ $activePost->category }}</span> {{ optional($activePost->published_at)->format('d M Y') }}</p>
    @if ($activePost->image_path)
      <img class="article-image" src="{{ asset($activePost->image_path) }}" alt="{{ $activePost->title }}">
    @endif
    <div class="rich-text">{!! $activePost->content ?: '<p>'.e($activePost->excerpt).'</p>' !!}</div>
  </article>
@else
  <div class="section-head">
    <div>
      <h2>Studio blog</h2>
      <p>Notes, techniques, and announcements from the studio.</p>
    </div>
    <div class="filter-row">
      <a class="segment {{ ! request('category') ? 'is-active' : '' }}" href="{{ route('home', ['view' => 'blog']) }}">All</a>
      @foreach ($blogCategories as $category)
        <a class="segment {{ request('category') === $category ? 'is-active' : '' }}" href="{{ route('home', ['view' => 'blog', 'category' => $category]) }}">{{ $category }}</a>
      @endforeach
    </div>
  </div>

  <div class="post-list narrow">
    @forelse ($posts->when(request('category'), fn ($c) => $c->where('category', request('category'))) as $post)
      <article class="post-card article-card">
        @if ($post->image_path) <img class="article-thumb" src="{{ asset($post->image_path) }}" alt=""> @endif
        <div>
          <h3><a href="{{ route('home', ['view' => 'blog', 'post' => $post->slug]) }}">{{ $post->title }}</a></h3>
          <p class="field-note"><span class="tag">{{ $post->category }}</span> {{ optional($post->published_at)->format('d M Y') }}</p>
          <p>{{ $post->excerpt }}</p>
          <a class="button" href="{{ route('home', ['view' => 'blog', 'post' => $post->slug]) }}">Read →</a>
        </div>
      </article>
    @empty
      <p class="field-note">No posts published yet — check back soon.</p>
    @endforelse
  </div>
@endif
